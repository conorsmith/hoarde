import {Form} from "./utility.js";

class MainController {
    constructor(eventBus, view, entities, gameId) {
        this.eventBus = eventBus;
        this.view = view;
        this.entities = entities;
        this.gameId = gameId;

        this.addEventListeners(this);
    }

    addEventListeners(controller) {
        $(this.view.el).on("show.bs.modal", function (e) {
            controller.onShow(e);
        });

        this.eventBus.addEventListener("sow.itemModified", this.onItemModified.bind(this));

        this.view.getSubmitButtonEl().addEventListener("click", this.onSubmit.bind(this));
    }

    onShow(e) {
        const controller = this;

        this.plot = new Plot(
            parseInt(this.view.el.dataset.capacityUsed, 10),
            parseInt(this.view.el.dataset.capacityAvailable, 10)
        );
        this.entityId = this.view.el.dataset.entityId;
        this.actorId = this.view.el.dataset.actorId;

        let inventory = new Inventory();

        this.entities.forEach(function (entity) {
            if (entity.inventory !== undefined) {
                entity.inventory.items.forEach(function (item) {
                    let sowAction = item.actions.find(function (action) {
                        return action.id === "99b65213-9cee-42ec-9dfe-8a04a790469e";
                    });
                    if (sowAction !== undefined) {
                        inventory.add(item);
                    }
                });
            }
        });

        this.view.repaint(this.plot);

        Object.values(inventory.items).forEach(function (item) {
            let plotItem = new PlotItem(item, 0);

            new SliderController(
                controller.eventBus,
                controller.view.createSliderView(item),
                plotItem
            );

            controller.plot.add(plotItem);
        });
    }

    onItemModified(e) {
        this.plot.add(e.detail.item);
        this.view.repaintCapacityBar(this.plot);
        this.view.repaintFooter(this.plot);
    }

    onSubmit(e) {
        Form.post(
            "/" + this.gameId + "/" + this.actorId + "/sow/" + this.entityId,
            {
                plot: JSON.stringify(this.plot.getContents())
            }
        );
    }
}
