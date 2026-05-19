(function () {
    "use strict";

    var FALLBACK_VISUAL_DEFAULTS = {
        jet_gap: 0,
        jet_button_scheme: 0,
        jet_btn_text: "Купи на изплащане с",
        jet_btn_text_card: "На вноски с твоята кредитна карта",
        jet_btn_logo: 1,
        jet_btn_max_width: 570,
        jet_btn_round: 16,
        jet_btn_font: 14,
    };

    var creditjetVisualPreviewApi = null;

    function ready(fn) {
        if (document.readyState !== "loading") {
            fn();
        } else {
            document.addEventListener("DOMContentLoaded", fn);
        }
    }

    function debounce(fn, delayMs) {
        var timer = null;
        return function () {
            var context = this;
            var args = arguments;
            if (timer !== null) {
                clearTimeout(timer);
            }
            timer = setTimeout(function () {
                timer = null;
                fn.apply(context, args);
            }, delayMs);
        };
    }

    function readJsonHiddenInput(elementId, fallback) {
        var el = document.getElementById(elementId);
        if (!el) {
            return fallback;
        }
        var raw = el.value || el.textContent || "";
        if (!raw) {
            return fallback;
        }
        try {
            return JSON.parse(raw);
        } catch (e) {
            return fallback;
        }
    }

    function getResolvedVisualDefaults() {
        var parsed = readJsonHiddenInput("creditjet-visual-defaults-data", {});
        if (!parsed || typeof parsed !== "object") {
            parsed = {};
        }
        return Object.assign({}, FALLBACK_VISUAL_DEFAULTS, parsed);
    }

    function fireFieldEvents(el) {
        if (!el) {
            return;
        }
        try {
            el.dispatchEvent(new Event("input", { bubbles: true }));
            el.dispatchEvent(new Event("change", { bubbles: true }));
        } catch (e) {
            // ignore
        }
        if (window.jQuery) {
            window.jQuery(el).trigger("change");
        }
    }

    function findVisualField(fieldName, elementId) {
        if (elementId) {
            var byId = document.getElementById(elementId);
            if (byId) {
                return byId;
            }
        }
        var form = document.getElementById("creditjet-settings-form");
        var scopes = [];
        if (form) {
            scopes.push(form);
        }
        var visualRoot = document.getElementById("creditjet-visual-settings");
        if (visualRoot) {
            scopes.push(visualRoot);
        }
        scopes.push(document);
        for (var i = 0; i < scopes.length; i++) {
            var found = scopes[i].querySelector(
                'input[name$="[' +
                    fieldName +
                    '"], select[name$="[' +
                    fieldName +
                    '"], textarea[name$="[' +
                    fieldName +
                    '"]',
            );
            if (found) {
                return found;
            }
        }
        return null;
    }

    var CONFIGURED_FORM_FIELD_ATTRS = {
        jet_btn_text: {
            idAttr: "data-jet-btn-text-id",
            nameAttr: "data-jet-btn-text-name",
        },
        jet_btn_text_card: {
            idAttr: "data-jet-btn-text-card-id",
            nameAttr: "data-jet-btn-text-card-name",
        },
        jet_btn_max_width: {
            idAttr: "data-jet-btn-max-width-id",
            nameAttr: "data-jet-btn-max-width-name",
        },
        jet_btn_round: {
            idAttr: "data-jet-btn-round-id",
            nameAttr: "data-jet-btn-round-name",
        },
        jet_btn_font: {
            idAttr: "data-jet-btn-font-id",
            nameAttr: "data-jet-btn-font-name",
        },
    };

    function findInputByExactName(fullName) {
        if (!fullName) {
            return null;
        }
        var form = document.getElementById("creditjet-settings-form");
        var scopes = form ? [form] : [document];
        for (var s = 0; s < scopes.length; s++) {
            var nodes = scopes[s].querySelectorAll("input, textarea, select");
            for (var i = 0; i < nodes.length; i++) {
                if (nodes[i].getAttribute("name") === fullName) {
                    return nodes[i];
                }
            }
        }
        return null;
    }

    function findConfiguredFormField(fieldName) {
        var visualRoot = document.getElementById("creditjet-visual-settings");
        var meta = CONFIGURED_FORM_FIELD_ATTRS[fieldName];
        if (visualRoot && meta) {
            var fieldId = visualRoot.getAttribute(meta.idAttr) || "";
            var fullName = visualRoot.getAttribute(meta.nameAttr) || "";
            if (fieldId) {
                var byId = document.getElementById(fieldId);
                if (byId) {
                    return byId;
                }
            }
            if (fullName) {
                var byName = findInputByExactName(fullName);
                if (byName) {
                    return byName;
                }
            }
        }
        return findVisualField(fieldName, null);
    }

    function findConfiguredTextField(which) {
        return findConfiguredFormField(
            which === "card" ? "jet_btn_text_card" : "jet_btn_text",
        );
    }

    function setTextFieldValue(which, value) {
        var el = findConfiguredTextField(which);
        if (el) {
            el.value = value;
            fireFieldEvents(el);
            if (window.jQuery) {
                window.jQuery(el).val(value).trigger("input change");
            }
        }

        var previewId =
            which === "card"
                ? "creditjet_btn_text_card_preview"
                : "creditjet_btn_text_preview";
        var previewEl = document.getElementById(previewId);
        if (previewEl) {
            previewEl.textContent = value;
        }
    }

    function resetVisualToDefaultsStandalone() {
        var d = getResolvedVisualDefaults();
        var root = document.getElementById("creditjet-visual-settings");

        var gapEl = findVisualField("jet_gap", null);
        if (gapEl) {
            gapEl.value = d.jet_gap;
            fireFieldEvents(gapEl);
        }

        var schemeIdx = parseInt(d.jet_button_scheme, 10) || 0;
        if (root) {
            var schemeRadio = root.querySelector(
                '.jet_scheme_radio[value="' + schemeIdx + '"]',
            );
            if (schemeRadio) {
                schemeRadio.checked = true;
                fireFieldEvents(schemeRadio);
            }
        }

        setTextFieldValue("main", d.jet_btn_text);
        setTextFieldValue("card", d.jet_btn_text_card);

        var logoVal = String(d.jet_btn_logo === undefined ? 1 : d.jet_btn_logo);
        var logoScope =
            document.getElementById("creditjet-settings-form") || document;
        var logoInput = logoScope.querySelector(
            'input[name*="[jet_btn_logo]"][value="' + logoVal + '"]',
        );
        if (logoInput) {
            logoInput.checked = true;
            fireFieldEvents(logoInput);
        }

        function setNumberAndRange(fieldName, rangeId, value) {
            var numEl = findConfiguredFormField(fieldName);
            var rangeEl = document.getElementById(rangeId);
            if (numEl) {
                numEl.value = value;
                fireFieldEvents(numEl);
            }
            if (rangeEl) {
                rangeEl.value = value;
            }
        }

        setNumberAndRange(
            "jet_btn_max_width",
            "creditjet_btn_max_width_range",
            d.jet_btn_max_width,
        );
        setNumberAndRange(
            "jet_btn_round",
            "creditjet_btn_round_range",
            d.jet_btn_round,
        );
        setNumberAndRange(
            "jet_btn_font",
            "creditjet_btn_font_range",
            d.jet_btn_font,
        );

        if (creditjetVisualPreviewApi) {
            creditjetVisualPreviewApi.applyScheme(schemeIdx);
            creditjetVisualPreviewApi.syncButtonType();
            creditjetVisualPreviewApi.syncTexts();
            creditjetVisualPreviewApi.syncLogo();
        }
    }

    window.creditjetResetVisualDefaults = function (event) {
        if (event && typeof event.preventDefault === "function") {
            event.preventDefault();
        }
        if (event && typeof event.stopPropagation === "function") {
            event.stopPropagation();
        }
        resetVisualToDefaultsStandalone();
    };

    ready(function () {
        var root = document.getElementById("creditjet-visual-settings");
        if (!root) {
            return;
        }

        var numberInputDebounceMs = 400;

        var schemeStyles = readJsonHiddenInput(
            "creditjet-scheme-styles-data",
            {},
        );
        var schemeLabels = readJsonHiddenInput(
            "creditjet-scheme-labels-data",
            [],
        );
        if (!schemeLabels || typeof schemeLabels.length === "undefined") {
            schemeLabels = [];
        }

        var initialScheme =
            parseInt(root.getAttribute("data-initial-scheme") || "0", 10) || 0;

        var visualDefaults = getResolvedVisualDefaults();

        var settingsForm =
            document.getElementById("creditjet-settings-form") || root;

        var previewStandard = document.getElementById(
            "creditjet-preview-standard",
        );
        var previewWide = document.getElementById("creditjet-preview-wide");
        var wideOnly = document.getElementById("creditjet-wide-only-settings");

        function getButtonTypeSelect() {
            var configuredId = root.getAttribute("data-button-type-id") || "";
            if (configuredId) {
                var byId = document.getElementById(configuredId);
                if (byId) {
                    return byId;
                }
            }

            return root.querySelector('select[name*="[jet_button_type]"]');
        }

        function setBlockVisible(element, visible) {
            if (!element) {
                return;
            }
            element.hidden = !visible;
            element.style.display = visible ? "" : "none";
        }

        function syncButtonTypePreview() {
            var buttonTypeSelect = getButtonTypeSelect();
            var isWide = buttonTypeSelect && buttonTypeSelect.value === "wide";
            setBlockVisible(previewStandard, !isWide);
            setBlockVisible(previewWide, isWide);
            setBlockVisible(wideOnly, isWide);
        }

        root.addEventListener("change", function (event) {
            var target = event.target;
            if (
                target &&
                target.matches &&
                target.matches('select[name*="[jet_button_type]"]')
            ) {
                syncButtonTypePreview();
            }
        });

        if (window.jQuery) {
            window
                .jQuery(root)
                .on(
                    "change",
                    'select[name*="[jet_button_type]"]',
                    syncButtonTypePreview,
                );
            window
                .jQuery(root)
                .on(
                    "select2:select",
                    'select[name*="[jet_button_type]"]',
                    syncButtonTypePreview,
                );
        }

        syncButtonTypePreview();

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

        function findFormInput(fieldName) {
            return findConfiguredFormField(fieldName);
        }

        function findFormNumberInput(fieldName) {
            return findFormInput(fieldName);
        }

        function valField(fieldName) {
            var el = findFormNumberInput(fieldName);
            return el ? el.value : "";
        }

        function buildPreviewStyle(schemeStyle) {
            return (
                (schemeStyle || "") +
                "--jet-wide-max-width:" +
                clampMaxWidth(valField("jet_btn_max_width")) +
                "px;" +
                "--jet-wide-radius:" +
                clampRound(valField("jet_btn_round")) +
                "px;" +
                "--jet-wide-font-size:" +
                clampFont(valField("jet_btn_font")) +
                "px;"
            );
        }

        function applyJetSchemePreview(idx) {
            var k = String(idx);
            var fullStyle = buildPreviewStyle(
                schemeStyles[k] || schemeStyles[idx] || "",
            );

            var wrapMain = document.getElementById(
                "creditjet-wide-preview-wrap",
            );
            var wrapCard = document.getElementById(
                "creditjet-wide-preview-wrap-card",
            );

            if (wrapMain) {
                wrapMain.setAttribute("style", fullStyle);
            }
            if (wrapCard) {
                wrapCard.setAttribute("style", fullStyle);
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

        function applyInitialWrapStyleFromData() {
            var initialWrapStyle =
                root.getAttribute("data-initial-wrap-style") || "";
            if (!initialWrapStyle) {
                return;
            }
            [
                "creditjet-wide-preview-wrap",
                "creditjet-wide-preview-wrap-card",
            ].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    el.setAttribute("style", initialWrapStyle);
                }
            });
        }

        applyInitialWrapStyleFromData();
        applyJetSchemePreview(initialScheme);

        function bindRangeSync(fieldName, rangeId, clampFn) {
            var num = findFormNumberInput(fieldName);
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
            var syncFromNumberDebounced = debounce(
                syncFromNumber,
                numberInputDebounceMs,
            );
            num.addEventListener("input", syncFromNumberDebounced);
            num.addEventListener("change", syncFromNumber);
            num.addEventListener("blur", syncFromNumber);
            range.addEventListener("input", syncFromRange);
            range.addEventListener("change", syncFromRange);
        }

        function getSelectedScheme() {
            var checked = root.querySelector(".jet_scheme_radio:checked");
            return checked ? parseInt(checked.value, 10) || 0 : 0;
        }

        bindRangeSync(
            "jet_btn_max_width",
            "creditjet_btn_max_width_range",
            clampMaxWidth,
        );
        bindRangeSync("jet_btn_round", "creditjet_btn_round_range", clampRound);
        bindRangeSync("jet_btn_font", "creditjet_btn_font_range", clampFont);

        var defaultJetBtnText =
            visualDefaults.jet_btn_text || "Купи на изплащане с";
        var defaultJetBtnTextCard =
            visualDefaults.jet_btn_text_card ||
            "На вноски с твоята кредитна карта";

        function syncJetBtnTextPreview() {
            var el = document.getElementById("creditjet_btn_text_preview");
            var input = findFormInput("jet_btn_text");
            if (!el || !input) {
                return;
            }
            var t = (input.value || "").trim();
            el.textContent = t === "" ? defaultJetBtnText : t;
        }

        function syncJetBtnTextCardPreview() {
            var el = document.getElementById("creditjet_btn_text_card_preview");
            var inputCard = findFormInput("jet_btn_text_card");
            if (!el || !inputCard) {
                return;
            }
            var t = (inputCard.value || "").trim();
            el.textContent = t === "" ? defaultJetBtnTextCard : t;
        }

        var btnText = findFormInput("jet_btn_text");
        var btnTextCard = findFormInput("jet_btn_text_card");
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

        function isJetBtnLogoEnabled() {
            var checked = root.querySelector(
                'input[name*="[jet_btn_logo]"]:checked',
            );
            if (!checked) {
                return true;
            }

            var value = String(checked.value).toLowerCase();
            return value === "1" || value === "true" || value === "on";
        }

        function syncJetBtnLogoPreview() {
            var on = isJetBtnLogoEnabled();
            root.querySelectorAll(".creditjet-wide-preview-logo").forEach(
                function (img) {
                    img.hidden = !on;
                },
            );
        }

        root.querySelectorAll('input[name*="[jet_btn_logo]"]').forEach(
            function (input) {
                input.addEventListener("change", syncJetBtnLogoPreview);
            },
        );
        syncJetBtnLogoPreview();

        creditjetVisualPreviewApi = {
            applyScheme: function (schemeIdx) {
                applyJetSchemePreview(schemeIdx);
            },
            syncButtonType: syncButtonTypePreview,
            syncTexts: function () {
                syncJetBtnTextPreview();
                syncJetBtnTextCardPreview();
            },
            syncLogo: syncJetBtnLogoPreview,
        };

        var resetBtn = document.getElementById(
            "creditjet-reset-visual-defaults",
        );
        if (resetBtn) {
            resetBtn.addEventListener(
                "click",
                window.creditjetResetVisualDefaults,
            );
        }
    });
})();
