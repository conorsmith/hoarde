class ModalView {
    constructor(el, constructionCardTemplate) {
        this.el = el;
        this.constructionCardTemplate = constructionCardTemplate;
    }

    repaint(action) {
        this.el.querySelector(".modal-title").innerText = action.label;
        this.el.querySelector(".modal-body").innerHTML = "";
    }

    createConstructionView() {
        this.el.querySelector(".modal-body").appendChild(
            this.constructionCardTemplate.content.cloneNode(true)
        );

        return new ConstructionView(this.el.querySelector(".modal-body").lastElementChild);
    }
}
