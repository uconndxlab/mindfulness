class ScormApiWrapper {
    constructor(packageId) {
        this.packageId = packageId;
        this.sessionId = null;
        this.initialized = false;
        this.terminated = false;
        this.errorCode = 0;
        this.errorString = '';
        this.version = '1.2';
    }

    LMSInitialize(param = '') {
        if (this.initialized) {
            this.errorCode = 101;
            return 'false';
        }

        return axios.post(`/scorm/initialize/${this.packageId}`)
            .then(response => {
                this.initialized = true;
                this.sessionId = response.data.session_id;
                this.errorCode = 0;
                return 'true';
            })
            .catch(error => {
                this.errorCode = 101;
                return 'false';
            });
    }

    LMSFinish(param = '') {
        if (!this.initialized || this.terminated) {
            this.errorCode = 301;
            return 'false';
        }

        return axios.post(`/scorm/terminate/${this.sessionId}`)
            .then(() => {
                this.terminated = true;
                this.errorCode = 0;
                return 'true';
            })
            .catch(() => {
                this.errorCode = 301;
                return 'false';
            });
    }

    LMSGetValue(element) {
        if (!this.initialized || this.terminated) {
            this.errorCode = 301;
            return '';
        }

        return axios.get(`/scorm/getValue/${this.sessionId}`, { params: { key: element } })
            .then(response => {
                this.errorCode = 0;
                return response.data.value || '';
            })
            .catch(() => {
                this.errorCode = 301;
                return '';
            });
    }

    LMSSetValue(element, value) {
        if (!this.initialized || this.terminated) {
            this.errorCode = 301;
            return 'false';
        }

        return axios.post(`/scorm/setValue/${this.sessionId}`, { key: element, value })
            .then(() => {
                this.errorCode = 0;
                return 'true';
            })
            .catch(() => {
                this.errorCode = 301;
                return 'false';
            });
    }

    LMSCommit(param = '') {
        if (!this.initialized || this.terminated) {
            this.errorCode = 301;
            return 'false';
        }

        return axios.post(`/scorm/commit/${this.sessionId}`)
            .then(() => {
                this.errorCode = 0;
                return 'true';
            })
            .catch(() => {
                this.errorCode = 301;
                return 'false';
            });
    }

    LMSGetLastError() {
        return this.errorCode.toString();
    }

    LMSGetErrorString(errorCode) {
        const errorStrings = {
            '0': 'No error',
            '101': 'General initialization failure',
            '201': 'Invalid argument error',
            '202': 'Element cannot have children',
            '203': 'Element not an array - cannot have count',
            '301': 'Not initialized',
            '401': 'Not implemented error',
            '402': 'Invalid set value, element is a keyword',
            '403': 'Element is read only',
            '404': 'Element is write only',
            '405': 'Incorrect data type'
        };
        return errorStrings[errorCode] || 'Unknown error';
    }

    LMSGetDiagnostic(errorCode) {
        return this.LMSGetErrorString(errorCode);
    }
} 