const CREDITJET_TAB_STORAGE_KEY = "creditjet_configuration_active_tab";

const CREDITJET_VALID_TABS = [
    "creditjet-management",
    "creditjet-functional",
    "creditjet-visual",
    "creditjet-interest-filters",
];

const create_creditjet_schema = () => {
    if (
        $("#jet_product_id").val() !== "" ||
        $("#jet_product_meseci").val() !== "" ||
        $("#jet_product_price").val() !== "" ||
        $("#jet_product_start").val() !== "" ||
        $("#jet_product_end").val() !== ""
    ) {
        creditjetPersistActiveTab();

        $.ajax({
            url: $("#link_to_create_schema_creditjet").val(),
            type: "POST",
            dataType: "json",
            data: {
                jet_product_id: $("#jet_product_id").val(),
                jet_product_percent: $(
                    "#jet_product_percent option:selected",
                ).val(),
                jet_product_meseci: $("#jet_product_meseci").val(),
                jet_product_price: $("#jet_product_price").val(),
                jet_product_start: $("#jet_product_start").val(),
                jet_product_end: $("#jet_product_end").val(),
            },
            success: function (json) {
                if (json.success == "success") {
                    alert(json.text);
                    window.location.reload();
                } else {
                    alert(json.text);
                }
            },
        });
    } else {
        alert("Имате непопълнени полета!");
    }
};

const delete_creditjet_schema = (id) => {
    $.ajax({
        url: $("#link_to_delete_schema_creditjet").val(),
        type: "POST",
        dataType: "json",
        data: {
            jet_product_id: id,
        },
        success: function (json) {
            if (json.success == "success") {
                alert("Успешно изтрихте филтъра.");
                window.location.reload();
            }
        },
    });
};

const creditjetIsValidTab = (tabId) => {
    return CREDITJET_VALID_TABS.indexOf(tabId) !== -1;
};

const creditjetSaveTabToSession = (tabId) => {
    if (!creditjetIsValidTab(tabId)) {
        return;
    }

    try {
        sessionStorage.setItem(CREDITJET_TAB_STORAGE_KEY, tabId);
    } catch (e) {
        // ignore private mode / blocked storage
    }
};

const creditjetGetTabFromSession = () => {
    try {
        const tabId = sessionStorage.getItem(CREDITJET_TAB_STORAGE_KEY);
        if (tabId && creditjetIsValidTab(tabId)) {
            return tabId;
        }
    } catch (e) {
        // ignore
    }

    return null;
};

const creditjetGetTabIdFromLink = ($link) => {
    const href = $link.attr("href");
    if (href && href.charAt(0) === "#") {
        return href.substring(1);
    }

    return null;
};

const creditjetReadActiveTabFromNav = () => {
    const $activeLinks = $(
        ".creditjet-config-form-wrapper .nav-tabs .nav-link.active",
    );

    if ($activeLinks.length) {
        return creditjetGetTabIdFromLink($activeLinks.last());
    }

    return null;
};

const creditjetResolveActiveTab = () => {
    if (creditjetIsValidTab(creditjetActiveTabId)) {
        return creditjetActiveTabId;
    }

    const fromSession = creditjetGetTabFromSession();
    if (fromSession) {
        return fromSession;
    }

    const fromNav = creditjetReadActiveTabFromNav();
    if (fromNav) {
        return fromNav;
    }

    const fromInput = $("#creditjet_active_tab").val();
    if (fromInput && creditjetIsValidTab(String(fromInput))) {
        return String(fromInput);
    }

    return "creditjet-management";
};

const creditjetSetHiddenActiveTab = (tabId) => {
    $("#creditjet_active_tab").val(tabId);
};

const creditjetActivateTab = (tabId) => {
    if (!creditjetIsValidTab(tabId)) {
        return;
    }

    const $tabLink = $(
        '.creditjet-config-form-wrapper .nav-tabs a[data-toggle="tab"][href="#' +
            tabId +
            '"]',
    );

    if ($tabLink.length && typeof $tabLink.tab === "function") {
        $tabLink.tab("show");
    }
};

const creditjetPersistActiveTab = () => {
    const tabId = creditjetResolveActiveTab();
    creditjetSaveTabToSession(tabId);
    creditjetSetHiddenActiveTab(tabId);

    return tabId;
};

const creditjetRestoreActiveTabOnLoad = () => {
    const sessionTab = creditjetGetTabFromSession();
    const inputTab = String($("#creditjet_active_tab").val() || "");

    let tabId = "creditjet-management";
    if (sessionTab) {
        tabId = sessionTab;
    } else if (creditjetIsValidTab(inputTab)) {
        tabId = inputTab;
    }

    creditjetSaveTabToSession(tabId);
    creditjetSetHiddenActiveTab(tabId);
    creditjetActivateTab(tabId);
};

$(document).ready(function () {
    creditjetRestoreActiveTabOnLoad();

    const $settingsForm = $("#creditjet-settings-form");

    $(".creditjet-config-form-wrapper").on(
        "mousedown click",
        '.nav-tabs a[data-toggle="tab"]',
        function () {
            const tabId = creditjetGetTabIdFromLink($(this));
            if (creditjetIsValidTab(tabId)) {
                creditjetSaveTabToSession(tabId);
                creditjetSetHiddenActiveTab(tabId);
                creditjetActiveTabId = tabId;
            }
        },
    );

    $(".creditjet-config-form-wrapper").on(
        "shown.bs.tab",
        'a[data-toggle="tab"]',
        function (e) {
            const href = $(e.target).attr("href");
            if (href && href.charAt(0) === "#") {
                const tabId = href.substring(1);
                creditjetSaveTabToSession(tabId);
                creditjetSetHiddenActiveTab(tabId);
            }
        },
    );

    $settingsForm.on("submit", function () {
        creditjetPersistActiveTab();
    });

    $("#save-button").on("mousedown click", function () {
        creditjetPersistActiveTab();
    });

    $(document).on("click", ".btn-delete-creditjet-schema", function () {
        const id = $(this).data("jet-product-id");
        if (id !== undefined && id !== "") {
            delete_creditjet_schema(String(id));
        }
    });

    window.prestashop.component.initComponents([
        "ChoiceTable",
        "MultipleChoiceTable",
    ]);

    new window.prestashop.component.ChoiceTree(
        "#form_category_choice_tree_type",
    );
    new window.prestashop.component.ChoiceTree(
        "#form_material_choice_tree_type",
    );
    new window.prestashop.component.ChoiceTree(
        "#form_shop_choices_tree_type",
    ).enableAutoCheckChildren();
});
