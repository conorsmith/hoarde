class ScavengeError {
    constructor(el) {
        this.el = el;
    }

    attachMessage(message) {
        this.message = message;
    }

    repaint() {
        if (this.message === undefined) {
            var previousAlert = this.el.querySelector(".alert");

            if (previousAlert) {
                previousAlert.remove();
            }

            return;
        }

        var alert = document.createElement("div");
        alert.classList.add("alert");
        alert.classList.add("alert-danger");
        alert.style.marginBottom = "1rem";
        alert.innerHTML = this.message;

        this.el.appendChild(alert);
    }
}
