class EventBus {
    constructor() {
        this.bus = document.createElement("bus");
    }

    addEventListener(event, callback) {
        this.bus.addEventListener(event, callback);
    }

    removeEventListener(event, callback) {
        this.bus.removeEventListener(event, callback);
    }

    dispatchEvent(eventName, detail = {}) {
        this.bus.dispatchEvent(new CustomEvent(eventName, {
            detail: detail
        }));
    }
}
