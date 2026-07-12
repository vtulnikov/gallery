"use strict";

export default class View {
    static container = document.querySelector(".container");
    static cover = document.querySelector(".cover");
    static galleryPopup = document.querySelector(".gallery-popup");
    static popupClose = document.querySelector(".gallery-popup_close");

    static createConfirmPopup(text, time) {
        const div = document.createElement("div");
        div.setAttribute("id", "confirm-popup");
        const p = document.createElement("p");
        p.textContent = text;
        div.appendChild(p);
        this.container.append(div);
        setTimeout(() => {
            div.remove();
        }, time);
    }
}
