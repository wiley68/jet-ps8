const create_creditjet_schema = () => {
    if (
        $("#jet_product_id").val() !== "" ||
        $("#jet_product_meseci").val() !== "" ||
        $("#jet_product_price").val() !== "" ||
        $("#jet_product_start").val() !== "" ||
        $("#jet_product_end").val() !== ""
    ) {
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

const creditjetGetActiveConfigurationTab = () => {
    const $activeTab = $(
        ".creditjet-config-form-wrapper .nav-tabs .nav-link.active",
    );
    if (!$activeTab.length) {
        return "creditjet-management";
    }

    const href = $activeTab.attr("href");
    if (href && href.charAt(0) === "#") {
        return href.substring(1);
    }

    return "creditjet-management";
};

$(document).ready(function () {
    $("#creditjet-settings-form").on("submit", function () {
        $("#creditjet_active_tab").val(creditjetGetActiveConfigurationTab());
    });

    $(".creditjet-config-form-wrapper .nav-tabs .nav-link").on(
        "shown.bs.tab",
        function () {
            $("#creditjet_active_tab").val(
                creditjetGetActiveConfigurationTab(),
            );
        },
    );

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
