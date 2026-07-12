"use strict";

import Config from "./Config.js";
import Api from "./Api.js";
import View from "./View.js";

document.addEventListener("DOMContentLoaded", () => {
    View.container.addEventListener("click", async (e) => {
        const sendFileBtn = e.target.closest('button[name="sendfile"]');
        if (sendFileBtn) {
            const formGroup = sendFileBtn.closest(".form-group");
            const form = sendFileBtn.closest("form");
            e.preventDefault();

            const data = new FormData(form);
            const oversized = [];

            for (let [key, value] of data) {
                if (!(value instanceof File)) continue;

                if (value.size > Config.maxFileSize) {
                    oversized.push({
                        name: value.name,
                        size: value.size,
                        sizeMb: (value.size / 1024 / 1024).toFixed(2),
                    });
                }
            }
            if (oversized.length > 0) {
                let err = "Слишком большие файлы: ";
                console.error("Найдены слишком большие файлы");
                oversized.forEach((img) => {
                    err += img.name + " ";
                    console.error(
                        `Файл ${img.name} слишком большой, его размер ${img.sizeMb} Mb`,
                    );
                });
                setTimeout(() => {
                    View.createConfirmPopup(err, 5000);
                }, 100);
                throw new Error(`"Найдены слишком большие файлы`);
            }

            data.append("action", "upload");
            const result = await Api.send(data);

            if (result?.success) {
                setTimeout(() => {
                    View.createConfirmPopup(result["success"], 2000);
                }, 100);
                form.reset();
            }
            return;
        }

        const resizeBtn = e.target.closest('button[name="resizeimages"]');
        if (resizeBtn) {
            e.preventDefault();
            const form = resizeBtn.closest("form");
            const data = new FormData(form);
            data.append("action", "resize");
            const result = await Api.send(data);

            if (result?.success) {
                setTimeout(() => {
                    View.createConfirmPopup(result["success"], 2000);
                }, 100);
            }
            return;
        }
        const showGallery = e.target.closest('button[name="showimages"]');
        if (showGallery) {
            e.preventDefault();
            const form = showGallery.closest("form");
            const data = new FormData(form);
            data.append("action", "get");
            const result = await Api.send(data);
            if (result.length < 1) {
                console.error("Пустая папка");
            }
            View.cover.classList.remove("hidden");
            View.galleryPopup.innerHTML = result;

            return;
        }
        return;
    });
    View.popupClose.addEventListener("click", (e) => {
        View.cover.classList.add("hidden");
    });
    View.cover.addEventListener("click", (e) => {
        if (e.target === e.currentTarget) {
            View.cover.classList.add("hidden");
        }
    });
    //копируем урл по клику на картинку
    View.galleryPopup.addEventListener("click", async (e) => {
        const img = e.target.closest("img");
        if (!img) return;
        try {
            await navigator.clipboard.writeText(img.src);
            console.log("URL скопирован:", img.src);
        } catch (err) {
            console.error("Ошибка:", err);
        }
    });
});
