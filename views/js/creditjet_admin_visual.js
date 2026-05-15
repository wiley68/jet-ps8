(function () {
    "use strict";

    function ready(fn) {
        if (document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    ready(function () {
        var root = document.getElementById("creditjet-visual-settings");
        if (!root) {
            return;
        }

        var schemeStyles = {};
        var schemeLabels = [];
        try {
            schemeStyles = JSON.parse(
                root.getAttribute("data-scheme-styles") || "{}",
            );
            schemeLabels = JSON.parse(
                root.getAttribute("data-scheme-labels") || "[]",
            );
        } catch (e) {
            schemeStyles = {};
            schemeLabels = [];
        }

        var initialScheme =
            parseInt(root.getAttribute("data-initial-scheme") || "0", 10) || 0;

        var buttonTypeSelect = document.getElementById(
            root.getAttribute("data-button-type-id") || "",
        );
        var previewStandard = document.getElementById(
            "creditjet-preview-standard",
        );
        var previewWide = document.getElementById("creditjet-preview-wide");
        var wideOnly = document.getElementById("creditjet-wide-only-settings");

        function syncButtonTypePreview() {
            var isWide = buttonTypeSelect && buttonTypeSelect.value === "wide";
            if (previewStandard) {
                previewStandard.hidden = isWide;
            }
            if (previewWide) {
                previewWide.hidden = !isWide;
            }
            if (wideOnly) {
                wideOnly.hidden = !isWide;
            }
        }

        if (buttonTypeSelect) {
            buttonTypeSelect.addEventListener("change", syncButtonTypePreview);
            syncButtonTypePreview();
        }

        function clampMaxWidth(v) {
            v = parseInt(v, 10);
            if (isNaN(v)) {
                v = 570;
            }
            if (v < 30) {
                v = 30;
            }
            if (v > 1200) {
                v = 1200;
            }
            return v;
        }

        function clampRound(v) {
            v = parseInt(v, 10);
            if (isNaN(v)) {
                v = 16;
            }
            if (v < 0) {
                v = 0;
            }
            if (v > 25) {
                v = 25;
            }
            return v;
        }

        function clampFont(v) {
            v = parseInt(v, 10);
            if (isNaN(v)) {
                v = 14;
            }
            if (v < 6) {
                v = 6;
            }
            if (v > 36) {
                v = 36;
            }
            return v;
        }

        function val(id) {
            var el = document.getElementById(id);
            return el ? el.value : "";
        }

        function applyJetSchemePreview(idx) {
            var k = String(idx);
            var s = schemeStyles[k] || schemeStyles[idx] || "";
            s +=
                "--jet-wide-max-width:" +
                clampMaxWidth(val("creditjet_btn_max_width")) +
                "px;";
            s +=
                "--jet-wide-radius:" +
                clampRound(val("creditjet_btn_round")) +
                "px;";
            s +=
                "--jet-wide-font-size:" +
                clampFont(val("creditjet_btn_font")) +
                "px;";

            var wrapMain = document.getElementById(
                "creditjet-wide-preview-wrap",
            );
            var wrapCard = document.getElementById(
                "creditjet-wide-preview-wrap-card",
            );
            if (wrapMain) {
                wrapMain.setAttribute("style", s);
            }
            if (wrapCard) {
                wrapCard.setAttribute("style", s);
            }

            var summary = document.getElementById(
                "creditjet_scheme_selected_summary",
            );
            if (summary) {
                var lab =
                    schemeLabels[idx] !== undefined &&
                    schemeLabels[idx] !== null
                        ? schemeLabels[idx]
                        : "";
                summary.textContent = "Избрана визуална схема: " + lab;
            }
        }

        root.querySelectorAll(".jet_scheme_radio").forEach(function (radio) {
            radio.addEventListener("change", function () {
                applyJetSchemePreview(parseInt(radio.value, 10) || 0);
            });
        });

        applyJetSchemePreview(initialScheme);

        function bindRangeSync(numberId, rangeId, clampFn) {
            var num = document.getElementById(numberId);
            var range = document.getElementById(rangeId);
            if (!num || !range) {
                return;
            }
            function syncFromNumber() {
                var t = clampFn(num.value);
                num.value = t;
                range.value = t;
                applyJetSchemePreview(getSelectedScheme());
            }
            function syncFromRange() {
                var t = clampFn(range.value);
                range.value = t;
                num.value = t;
                applyJetSchemePreview(getSelectedScheme());
            }
            num.addEventListener("input", syncFromNumber);
            num.addEventListener("change", syncFromNumber);
            range.addEventListener("input", syncFromRange);
            range.addEventListener("change", syncFromRange);
        }

        function getSelectedScheme() {
            var checked = root.querySelector(".jet_scheme_radio:checked");
            return checked ? parseInt(checked.value, 10) || 0 : 0;
        }

        bindRangeSync(
            "creditjet_btn_max_width",
            "creditjet_btn_max_width_range",
            clampMaxWidth,
        );
        bindRangeSync(
            "creditjet_btn_round",
            "creditjet_btn_round_range",
            clampRound,
        );
        bindRangeSync(
            "creditjet_btn_font",
            "creditjet_btn_font_range",
            clampFont,
        );

        var defaultJetBtnText = "Купи на изплащане с";
        var defaultJetBtnTextCard = "На вноски с твоята кредитна карта";

        function syncJetBtnTextPreview() {
            var el = document.getElementById("creditjet_btn_text_preview");
            var input = document.getElementById("creditjet_btn_text");
            if (!el || !input) {
                return;
            }
            var t = (input.value || "").trim();
            el.textContent = t === "" ? defaultJetBtnText : t;
        }

        function syncJetBtnTextCardPreview() {
            var el = document.getElementById("creditjet_btn_text_card_preview");
            var inputCard = document.getElementById("creditjet_btn_text_card");
            if (!el || !inputCard) {
                return;
            }
            var t = (inputCard.value || "").trim();
            el.textContent = t === "" ? defaultJetBtnTextCard : t;
        }

        var btnText = document.getElementById("creditjet_btn_text");
        var btnTextCard = document.getElementById("creditjet_btn_text_card");
        if (btnText) {
            btnText.addEventListener("input", syncJetBtnTextPreview);
            btnText.addEventListener("change", syncJetBtnTextPreview);
        }
        if (btnTextCard) {
            btnTextCard.addEventListener("input", syncJetBtnTextCardPreview);
            btnTextCard.addEventListener("change", syncJetBtnTextCardPreview);
        }
        syncJetBtnTextPreview();
        syncJetBtnTextCardPreview();

        var btnLogo =
            document.getElementById("creditjet_btn_logo") ||
            root.querySelector('input[name*="[jet_btn_logo]"]');
        function syncJetBtnLogoPreview() {
            var on = btnLogo && btnLogo.checked;
            root.querySelectorAll(".creditjet-wide-preview-logo").forEach(
                function (img) {
                    img.hidden = !on;
                },
            );
        }
        if (btnLogo) {
            btnLogo.addEventListener("change", syncJetBtnLogoPreview);
            syncJetBtnLogoPreview();
        }
    });
})();
