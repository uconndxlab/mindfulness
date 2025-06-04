<?php

namespace App\Services;

use PhpZip\ZipFile;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class XapiService
{
    public function processPackage(UploadedFile $file)
    {
        // generate unique id for package
        $packageId = Str::random(20);
        // extraction path
        $extractPath = Storage::disk('xapi_content')->path("extracted/{$packageId}");

        try {
            // store zip in private local
            $zipRelPath = "xapi_packages/{$packageId}.zip";
            Storage::disk('local')->put($zipRelPath, $file->get());
            $zipAbsPath = Storage::disk('local')->path($zipRelPath);

            // make extraction dir
            if (!file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // extract zip
            $zipFile = new ZipFile();
            $zipFile->openFile($zipAbsPath)->extractTo($extractPath);
            $zipFile->close();

            // find TinCan manifest
            $manifestPath = $this->findTincanXml($extractPath);
            if (!$manifestPath) {
                throw new \Exception('No tincan.xml found in the package.');
            }

            // parse
            $manifestData = $this->parseTincanXml($manifestPath);

            return [
                'success' => true,
                'package_id' => $packageId,
                'entry_point' => $manifestData['entry_point'],
                'xapi_activity_id' => $manifestData['xapi_activity_id']
            ];

        } catch (\Exception $e) {
            if ($extractPath && file_exists($extractPath)) {
                $this->removeDirectory($extractPath);
            }
            if (isset($zipRelPath) && Storage::disk('local')->exists($zipRelPath)) {
                Storage::disk('local')->delete($zipRelPath);
            }
            throw $e;
        }
    }

    private function findTincanXml($directory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'tincan.xml') {
                return $file->getPathname();
            }
        }
        return null;
    }

    private function parseTincanXml($manifestPath)
    {
        $xml = simplexml_load_file($manifestPath);
        if ($xml === false) {
            throw new \Exception('Invalid tincan.xml file: ' . $manifestPath);
        }

        $result = ['entry_point' => null, 'xapi_activity_id' => null];

        // register default TinCan namespace
        $xml->registerXPathNamespace('tc', 'http://projecttincan.com/tincan.xsd');

        // get entry from course activity
        $courseLaunchNodes = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']/tc:launch");
        if (!empty($courseLaunchNodes)) {
            $result['entry_point'] = (string)$courseLaunchNodes[0];
        }

        // get activity id from course activity id attribute
        $courseIdNodes = $xml->xpath("//tc:activities/tc:activity[@type='http://adlnet.gov/expapi/activities/course']/@id");
        if (!empty($courseIdNodes)) {
            $result['xapi_activity_id'] = (string)$courseIdNodes[0];
        }

        // fallback
        if (empty($result['entry_point'])) {
            // find any launch element within any activity
            $anyLaunchNodes = $xml->xpath("//tc:activities/tc:activity/tc:launch");
            if (!empty($anyLaunchNodes)) {
                $result['entry_point'] = (string)$anyLaunchNodes[0];
            }
        }
        if (empty($result['xapi_activity_id'])) {
            // get activity id from first activity
            $anyActivityIdNodes = $xml->xpath("(//tc:activities/tc:activity)[1]/@id");
            if (!empty($anyActivityIdNodes)) {
                $result['xapi_activity_id'] = (string)$anyActivityIdNodes[0];
            }
        }

        // throw error if no entry point or activity id
        if (empty($result['entry_point'])) {
            throw new \Exception('No launch URL found in tincan.xml: ' . $manifestPath);
        }
        if (empty($result['xapi_activity_id'])) {
            throw new \Exception('No activity ID found in tincan.xml: ' . $manifestPath);
        }
        return $result;
    }

    private function removeDirectory($directory)
    {
        if (!file_exists($directory)) return;
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
}