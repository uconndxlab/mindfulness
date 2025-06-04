<?php

namespace App\Services;

use PhpZip\ZipFile;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ScormService
{
    public function processPackage(UploadedFile $file)
    {
        // Generate unique directory name for this package
        $packageId = Str::random(20);
        $extractPath = storage_path("app/public/scorm/extracted/{$packageId}");
        $zipPath = null;
        
        try {
            // Store the original zip file
            $zipPath = $file->storeAs(
                'scorm/packages', 
                $packageId . '.zip', 
                'local'
            );

            // Create extraction directory if it doesn't exist
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // Extract the zip file
            $zipFile = new ZipFile();
            $zipFile->openFile(storage_path("app/{$zipPath}"))
                   ->extractTo($extractPath);
            $zipFile->close();

            // Find the manifest file
            $manifestInfo = $this->findManifestFile($extractPath);
            if (!$manifestInfo) {
                throw new \Exception('No imsmanifest.xml or tincan.xml found in package');
            }

            $manifestPath = $manifestInfo['path'];
            \Log::info('manifestPath', ['manifestPath' => $manifestPath]);
            $isXapi = $manifestInfo['filename'] === 'tincan.xml';

            $entryPoint = null;
            $packageType = $isXapi ? 'xapi' : 'scorm';
            $xapiActivityId = null;

            if ($isXapi) {
                // get entry point from xapi manifest and activity id
                $xapiManifest = $this->parseXapiManifest($manifestPath);
                $entryPoint = $xapiManifest['entry_point'];
                $xapiActivityId = $xapiManifest['activity_id'];
            } else {
                // Get the entry point from manifest
                $entryPoint = $this->parseScormManifest($manifestPath);
            }
            
            return [
                'success' => true,
                'package_id' => $packageId,
                'entry_point' => $entryPoint,
                'extract_path' => $extractPath,
                'type' => $packageType,
                'xapi_activity_id' => $xapiActivityId
            ];

        } catch (\Exception $e) {
            // Clean up on failure
            if ($extractPath && file_exists($extractPath)) {
                // $this->removeDirectory($extractPath);
            }
            if ($zipPath && Storage::disk('local')->exists($zipPath)) {
                // Storage::disk('local')->delete($zipPath);
            }
            
            throw $e; // Re-throw the exception after cleanup
        }
    }

    private function findManifestFile($directory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $filename = $file->getFilename();
            // look for SCORM manifest or TinCan/xAPI manifest
            if ($file->isFile() && ($filename === 'imsmanifest.xml' || $filename === 'tincan.xml')) {
                return ['path' => $file->getPathname(), 'filename' => $filename];
            }
        }
        return null;
    }

    private function parseScormManifest($manifestPath)
    {
        $xml = simplexml_load_file($manifestPath);
        if ($xml === false) {
            throw new \Exception('Invalid SCORM manifest file: ' . $manifestPath);
        }

        // get the version of the manifest
        $version = $xml->xpath('//imscp:manifest/@version');
        if (empty($version)) {
            throw new \Exception('No version found in SCORM manifest: ' . $manifestPath);
        }

        // get the version number
        $versionNumber = (string)$version[0];

        // check if the version is 1.2 or 2004 3rd Edition
        if ($versionNumber !== '1.2' && $versionNumber !== '2004 3rd Edition') {
            throw new \Exception('Unsupported SCORM version: ' . $versionNumber);
        }

        // Register the namespaces
        $xml->registerXPathNamespace('adlcp', 'http://www.adlnet.org/xsd/adlcp_rootv1p2');
        $xml->registerXPathNamespace('imscp', 'http://www.imsglobal.org/xsd/imscp_v1p1');

        // Try to find the resource with href/entry point in the first organization's item
        $resources = $xml->xpath('//imscp:organization[1]//imscp:item[1]/@identifierref');
        if (!empty($resources)) {
            $identifierRef = (string)$resources[0];
            $resourceHref = $xml->xpath("//imscp:resource[@identifier='{$identifierRef}']/@href");
            if (!empty($resourceHref)) {
                return (string)$resourceHref[0];
            }
        }
        
        // Fallback: Try to find the first resource with an href attribute (less reliable for SCORM)
        $resources = $xml->xpath('//imscp:resource[@href][1]/@href');
        if (!empty($resources)) {
            return (string)$resources[0];
        }

        // Fallback for some common structures if a specific SCORM type is known
        $scoResource = $xml->xpath('//imscp:resource[@href and @adlcp:scormtype="sco"][1]/@href');
        if(!empty($scoResource)) {
            return (string)$scoResource[0];
        }

        throw new \Exception('No valid entry point found in SCORM package: ' . $manifestPath);
    }

    private function removeDirectory($directory)
    {
        if (!file_exists($directory)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($directory);
    }

    private function parseXapiManifest($manifestPath)
    {
        // load manifest
        if (!file_exists($manifestPath) || !is_readable($manifestPath)) {
            throw new \Exception('TinCan/xAPI manifest file does not exist or is not readable: ' . $manifestPath);
        }
        $rawManifestContent = file_get_contents($manifestPath);
        $xml = simplexml_load_string($rawManifestContent);
        if ($xml === false) {
            throw new \Exception('Invalid TinCan/xAPI manifest file (could not be parsed): ' . $manifestPath);
        }

        // register default TinCan namespace
        $xml->registerXPathNamespace('tc', 'http://projecttincan.com/tincan.xsd');

        // get the first activity of type "http://adlnet.gov/expapi/activities/course" - should have launch element
        $courseActivity = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']");
        
        $result = [
            'entry_point' => null,
            'activity_id' => null
        ];

        if (!empty($courseActivity)) {
            // find launch element with lang attribute
            $launchNodes = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']/tc:launch[@lang]");
            if (!empty($launchNodes) && isset($launchNodes[0])) {
                $result['entry_point'] = (string)$launchNodes[0];
            }
            else {
                $launchNodes = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']/tc:launch");
                if (!empty($launchNodes) && isset($launchNodes[0])) {
                    $result['entry_point'] = (string)$launchNodes[0];
                }
            }

            // get activity id
            $activityIdNodes = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']/@id");
            if (!empty($activityIdNodes) && isset($activityIdNodes[0])) {
                $result['activity_id'] = (string)$activityIdNodes[0];
            }
        }

        // Final fallbacks if course activity not found
        if (!$result['entry_point']) {
            $launchNodes = $xml->xpath("//tc:activities/tc:activity/tc:launch");
            if (!empty($launchNodes) && isset($launchNodes[0])) {
                $result['entry_point'] = (string)$launchNodes[0];
            } else {
                throw new \Exception('No launch entry point found in TinCan/xAPI manifest: ' . $manifestPath);
            }
        }

        if (!$result['activity_id']) {
            $activityIdNodes = $xml->xpath("//tc:activities/tc:activity[1]/@id"); // Note: tc:activity
            if (!empty($activityIdNodes) && isset($activityIdNodes[0])) {
                $result['activity_id'] = (string)$activityIdNodes[0];
            } else {
                throw new \Exception('No activity id found in TinCan/xAPI manifest: ' . $manifestPath);
            }
        }

        return $result;
    }
}