<?php if (isset($data)) : ?>
    <?php
    //setup some variables for readability
    $_btn_type = se($data, "type", "button", false);
    $_btn_text = se($data, "text", "Button", false);
    $_btn_color = se($data, "color", "primary", false);
    $_btn_onclick = se($data, "onClick", "", false); // Retrieve onClick attribute if provided
    ?>
    <?php if ($_btn_type === "button") : ?>
        <button class="btn btn-<?php se($_btn_color); ?>" <?php if ($_btn_onclick) : ?>onclick="<?php se($_btn_onclick); ?>"<?php endif; ?>><?php se($_btn_text); ?></button>
    <?php elseif ($_btn_type === "submit") : ?>
        <input type="submit" class="btn btn-<?php se($_btn_color); ?>" value="<?php se($_btn_text); ?>" <?php if ($_btn_onclick) : ?>onclick="<?php se($_btn_onclick); ?>"<?php endif; ?> />
    <?php endif; ?>

    <?php
    //cleanup just in case this is used directly instead of via render_button()
    unset($_btn_type);
    unset($_btn_text);
    unset($_btn_color);
    unset($_btn_onclick); // Cleanup onClick
    ?>
<?php endif; ?>
