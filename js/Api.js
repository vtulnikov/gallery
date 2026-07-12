"use strict";

import Config from "./Config.js";

export default class Api {
    static async send(data) {
        const response = await fetch(Config.ajaxPath, {
            method: "POST",
            body: data,
        });
        let result = null;
        try {
            if (data.get("action") === "get") {
                result = await response.text();
            } else {
                result = await response.json();
            }
            // console.log(result);
        } catch (e) {
            throw new Error("Некорректный json");
        }
        if (!response.ok) {
            throw new Error(
                result?.error || "Что-то пошло не так " + response.status,
            );
        }
        return result;
    }
}
