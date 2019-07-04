class Form {
    static post(action, inputs) {
        const formEl = document.createElement("form");
        formEl.setAttribute("action", action);
        formEl.setAttribute("method", "POST");
        formEl.setAttribute("hidden", true);

        Object.entries(inputs).forEach(function ([key, value]) {

            let inputEl = document.createElement("input");
            inputEl.setAttribute("type", "hidden");
            inputEl.setAttribute("name", key);
            inputEl.setAttribute("value", value);
            formEl.appendChild(inputEl);

        });

        document.body.appendChild(formEl);

        formEl.submit();
    }
}
