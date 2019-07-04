class Spinner {
    static renderIn(el) {
        if (document.getElementById("spinner") === null) {
            console.error("Spinner template not found with ID #spinner.");
            return;
        }

        const template = document.getElementById("spinner").content.cloneNode(true);
        el.innerText = "";
        el.appendChild(template);
    }
}
