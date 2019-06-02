class Error {
    constructor(message) {
        this.message = message;
    }

    isEmpty() {
        return this.message === "";
    }
}