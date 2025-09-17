/**
 * Displays a setting or multiple ones as "block" or "none"
 * by its or their id.
 */
function setStyle(id, display) {
  if (!Array.isArray(id)) {
    id = [id];
  }

  for (let idIndividual of id) {
    let idInput       = idIndividual + "-input",
        idDescription = idIndividual + "-description";

    if (document.getElementById(idInput)) {
      document.getElementById(idInput).style.display = display;
    }

    if (document.getElementById(idDescription)) {
      document.getElementById(idDescription).style.display = display;
    }
  }
}

// An array of social sites supported by the theme.
function socialSites() {
  return ['bluesky', 'diaspora', 'email', 'facebook', 'instagram', 'mastodon', 'matrix', 'phone', 'signal', 'sms', 'youtube', 'whatsapp', 'x', 'other'];
}

// Toggles other reactions settings.
function toggleOtherReactionsSettings(id) {
  if (document.getElementById("social_" + id).value !== "") {
    if (document.getElementById("footer_enabled").checked) {
      setStyle("reactions_other_" + id, "block");
    }
  } else {
    setStyle("reactions_other_" + id, "none");
  }
}

// Toggles footer social settings.
function toggleFooterSocialSettings(id) {
  if (document.getElementById("social_" + id).value !== "" && document.getElementById("footer_enabled").checked) {
    if (document.getElementById("footer_enabled").checked) {
      setStyle("footer_social_" + id, "block");
    }
  } else {
    setStyle("footer_social_" + id, "none");
  }
}

// Shows or hides settings depending on other ones.
function disableInputs() {
  if (document.getElementById("global_color_primary").value === "custom") {
    document.getElementById("section-global-colors-light").style.display = "block";
    document.getElementById("section-global-colors-dark").style.display  = "block";

    setStyle(
      [
        "global_color_text_custom",
        "global_color_text_secondary_custom",
        "global_color_primary_custom",
        "global_color_primary_amplified_custom",
        "global_color_input_custom",
        "global_color_border_custom",
        "global_color_background_custom",
        "global_color_text_dark_custom",
        "global_color_text_secondary_dark_custom",
        "global_color_primary_dark_custom",
        "global_color_primary_dark_amplified_custom",
        "global_color_input_dark_custom",
        "global_color_border_dark_custom",
        "global_color_background_dark_custom"
      ],
      "block"
    );
  } else {
    document.getElementById("section-global-colors-light").style.display = "none";
    document.getElementById("section-global-colors-dark").style.display  = "none";

    setStyle(
      [
        "global_color_text_custom",
        "global_color_text_secondary_custom",
        "global_color_primary_custom",
        "global_color_primary_amplified_custom",
        "global_color_input_custom",
        "global_color_border_custom",
        "global_color_background_custom",
        "global_color_text_dark_custom",
        "global_color_text_secondary_dark_custom",
        "global_color_primary_dark_custom",
        "global_color_primary_dark_amplified_custom",
        "global_color_input_dark_custom",
        "global_color_border_dark_custom",
        "global_color_background_dark_custom"
      ],
      "none"
    );
  }

  if (document.getElementById("header_image-input").value
    || document.getElementById("header_image-preview")
  ) {
    setStyle(["header_image_position", "header_image_position", "header_image_description"], "block");
    setStyle("header_image", "none");

    document.getElementById("header_image-delete").style.display = "block";

    if (document.getElementById("header_image-retina")
      && document.getElementById("header_image-retina").style.display !== "none"
    ) {
      setStyle("header_image2x", "none");
    } else {
      setStyle("header_image2x", "block");
    }
  } else {
    setStyle(["header_image2x", "header_image_position", "header_image_description"], "none");
    setStyle("header_image", "block");

    document.getElementById("header_image-delete").style.display = "none";
  }

  if (document.getElementById("header_image_as_title").checked) {
    setStyle(["header_image_position", "header_image_description"], "none");
  } else {
    setStyle(["header_image_position", "header_image_description"], "block");
  }

  if (document.getElementById("content_postlist_type").value !== "content") {
    setStyle([
      "content_postlist_altcolor",
      "content_postlist_thumbnail"
      ],
      "block"
    );
  } else {
    setStyle([
      "content_postlist_altcolor",
      "content_postlist_thumbnail"
      ],
      "none"
    );
  }

  if (document.getElementById("reactions_other").value !== "disabled") {
    setStyle("reactions_other_email", "block");
  } else {
    setStyle("reactions_other_email", "none");
  }

  // Footer
  if (document.getElementById("footer_enabled").checked) {
    setStyle(
      [
        "footer_align",
        "footer_feed",
        "footer_credits"
      ],
      "block"
    );
  } else {
    setStyle(
      [
        "footer_align",
        "footer_feed",
        "footer_credits"
      ],
      "none"
    );
  }

  const socialSitesId = socialSites();

  var displayFooterSocialTitle = false;

  for (let siteId of socialSitesId) {
    if (document.getElementById("social_" + siteId).value && document.getElementById("footer_enabled").checked) {
      displayFooterSocialTitle = true;
    }

    toggleOtherReactionsSettings(siteId);
    toggleFooterSocialSettings(siteId);
  }

  if (displayFooterSocialTitle === true) {
    document.getElementById("section-footer-social").style.display = "block";
  } else {
    document.getElementById("section-footer-social").style.display = "none";
  }
}

/**
 * Displays the image submitted by the user.
 */
function changeImage(inputImgURL) {
  let img = inputImgURL.files[0];

  if (img) {
    let fileReader = new FileReader();
    fileReader.readAsDataURL(img);

    fileReader.addEventListener("load", function () {
      if (!document.getElementById("header_image-preview")) {
        var imgPreviewContainer = document.createElement('p');
        imgPreviewContainer.setAttribute("id", "header_image-preview");

        var imgPreview = document.createElement('img');
        imgPreview.setAttribute("id", "header_image-src");
        imgPreview.src = this.result;

        document.getElementById("header_image-description").after("", imgPreviewContainer);

        imgPreviewContainer.appendChild(imgPreview);
      } else {
        document.getElementById("header_image-src").src = this.result;

        if (document.getElementById("header_image-preview").style.display === "none") {
          document.getElementById("header_image-preview").style.display = "block";
        }
      }

      document.getElementById("header_image-delete-action").value = "false";

      setStyle(["header_image2x", "header_image_position", "header_image_description"], "block");
      setStyle("header_image", "none");

      document.getElementById("header_image-delete").style.display = "block";
    });
  }
}

function fontsPreview() {
  var fonts = {
    "system": "system-ui, ui-sans-serif, sans-serif",
    "sans-serif": "ui-sans-serif, sans-serif",
    "serif": "ui-serif, serif",
    "transitional": "Charter, \"Bitstream Charter\", \"Sitka Text\", Cambria, serif",
    "old-style": "\"Iowan Old Style\", \"Palatino Linotype\", \"URW Palladio L\", P052, serif",
    "garamond": "Garamond, Baskerville, \"Baskerville Old Face\", \"Hoefler Text\", \"Times New Roman\", serif",
    "humanist": "Seravek, \"Gill Sans Nova\", Ubuntu, Calibri, \"DejaVu Sans\", source-sans-pro, sans-serif",
    "geometric-humanist": "Avenir, Montserrat, Corbel, \"URW Gothic\", source-sans-pro, sans-serif",
    "classical-humanist": "Optima, Candara, \"Noto Sans\", source-sans-pro, sans-serif",
    "neo-grotesque": "Inter, Roboto, \"Helvetica Neue\", \"Arial Nova\", \"Nimbus Sans\", Arial, sans-serif",
    "monospace-slab-serif": "\"Nimbus Mono PS\", \"Courier New\", monospace",
    "monospace-code": "ui-monospace, \"Cascadia Code\", \"Source Code Pro\", Menlo, Consolas, \"DejaVu Sans Mono\", monospace",
    "industrial": "Bahnschrift, \"DIN Alternate\", \"Franklin Gothic Medium\", \"Nimbus Sans Narrow\", sans-serif-condensed, sans-serif",
    "rounded-sans": "ui-rounded, \"Hiragino Maru Gothic ProN\", Quicksand, Comfortaa, Manjari, \"Arial Rounded MT\", \"Arial Rounded MT Bold\", Calibri, source-sans-pro, sans-serif",
    "slab-serif": "Rockwell, \"Rockwell Nova\", \"Roboto Slab\", \"DejaVu Serif\", \"Sitka Small\", serif",
    "antique": "Superclarendon, \"Bookman Old Style\", \"URW Bookman\", \"URW Bookman L\", \"Georgia Pro\", Georgia, serif",
    "didone": "Didot, \"Bodoni MT\", \"Noto Serif Display\", \"URW Palladio L\", P052, Sylfaen, serif",
    "handwritten": "\"Segoe Print\", \"Bradley Hand\", Chilanka, TSCu_Comic, casual, cursive"
  }

  if (fonts[document.getElementById("global_font_family").value]) {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = fonts[document.getElementById("global_font_family").value];
  }

  if (document.getElementById("content_text_font").value !== "same") {
    document.getElementById("odyssey-config-content-font-preview").style.display = "block";

    if (fonts[document.getElementById("content_text_font").value]) {
      document.getElementById("odyssey-config-content-font-preview").style.fontFamily = fonts[document.getElementById("content_text_font").value];
    }
  } else {
    document.getElementById("odyssey-config-content-font-preview").style.display = "none";
  }
}

/**
 * Applies color change to the HTML5 picker and the text input.
 */
function changeColorInput(settingId, context) {
  let colorPicker  = document.getElementById(settingId).value,
      colorText    = document.getElementById(settingId + "-text").value;
      colorDefault = document.getElementById(settingId + "-default-value").value;

  if (colorPicker !== colorText && context !== "default") {
    if (context === "picker") {
      document.getElementById(settingId + "-text").value = colorPicker;
    } else if (context === "text") {
      if (colorText !== '') {
        document.getElementById(settingId).value = colorText;
      } else {
        document.getElementById(settingId).value = colorDefault;
      }
    }
  }

  if (context === "default") {
    document.getElementById(settingId).value           = colorDefault;
    document.getElementById(settingId + "-text").value = colorDefault;
  }
}

/**
 * Updates page width input range depending on width unit (em or px).
 */
function pageWidthInputDefault() {
  if (document.getElementById("global_unit").value === 'em') {
    document.getElementById("global_page_width_value").max  = document.getElementById("page_width_em_max_default").value;
    document.getElementById("global_page_width_value").min  = document.getElementById("page_width_em_min_default").value;
    document.getElementById("global_page_width_value").step = document.getElementById("page_width_em_step_default").value;
  } else if (document.getElementById("global_unit").value === 'px') {
    document.getElementById("global_page_width_value").max  = document.getElementById("page_width_px_max_default").value;
    document.getElementById("global_page_width_value").min  = document.getElementById("page_width_px_min_default").value;
    document.getElementById("global_page_width_value").step = document.getElementById("page_width_px_step_default").value;
  }
}

/**
 * Converts page width input depending on width unit (em or px).
 */
function pageWidthUnitChange(pageWidthValueCurrent, pageWidthUnitCurrent) {
  let pageWidthValueNew,
      pageWidthUnitNew;

  if (pageWidthUnitCurrent === "em") {
    pageWidthValueNew = (parseInt(pageWidthValueCurrent) * 16).toString();
    pageWidthUnitNew  = "px";

    document.getElementById("global_page_width_value").value = pageWidthValueNew;

    document.getElementById("global_page_width_value-output-value").innerHTML = pageWidthValueNew;
    document.getElementById("global_page_width_value-output-unit").innerHTML  = pageWidthUnitNew;
  } else if (pageWidthUnitCurrent === "px") {
    pageWidthValueNew = parseInt(parseInt(pageWidthValueCurrent) / 16).toString();
    pageWidthUnitNew  = "em";

    document.getElementById("global_page_width_value").value = pageWidthValueNew;

    document.getElementById("global_page_width_value-output-value").innerHTML = pageWidthValueNew;
    document.getElementById("global_page_width_value-output-unit").innerHTML  = pageWidthUnitNew;
  }
}

window.onload = function() {
  disableInputs();
  fontsPreview();

  window.onchange = function() {
    disableInputs();
    fontsPreview();
  };

  window.oninput = function() {
    disableInputs();
  };

  // Supports range inputs changes.
  var rangeInputs = document.querySelectorAll("input[type=range]");

  for (var i = 0; i < rangeInputs.length; i++) {
    if (rangeInputs[i].id !== "global_page_width_value") {
      // Page width setting is excluded.
      rangeInputs[i].onchange = function(rangeInput) {
        document.getElementById(rangeInput.target.id + "-output-value").innerHTML = rangeInput.target.value;
      };
    }
  }

  // Supports page width option (custom range input).
  pageWidthInputDefault()

  document.getElementById("global_page_width_value").oninput = function() {
    document.getElementById("global_page_width_value-output-value").innerHTML = document.getElementById("global_page_width_value").value;
  };

  let pageWidthValueCurrent = document.getElementById("global_page_width_value").value,
      pageWidthUnitCurrent  = document.getElementById("global_unit").value;

  document.getElementById("global_page_width_value").onchange = function() {
    pageWidthValueCurrent = document.getElementById("global_page_width_value").value;
  }

  document.getElementById("global_unit").onchange = function() {
    pageWidthInputDefault();

    pageWidthUnitChange(pageWidthValueCurrent, pageWidthUnitCurrent);

    pageWidthValueCurrent = document.getElementById("global_page_width_value").value;
    pageWidthUnitCurrent  = document.getElementById("global_unit").value;
  }

  /**
   * Puts choosen color with HTML5 code picker
   * in its associated input field,
   * or puts the color typed in the input color field
   * in the HTML color picker.
   *
   * @see function changeColorInput()
   */
  const colorSettings = document.getElementsByClassName("odyssey-color-setting");

  Array.prototype.forEach.call(colorSettings, function(colorSetting) {
    let settingInputId = colorSetting.getAttribute("id"),
        settingId      = "",
        textId         = "",
        defaultId      = "";

    if (settingInputId.substr(settingInputId.length - 6) === "-input") {
        settingId = settingInputId.substr(0, settingInputId.length - 6),
        textId  = settingId + "-text";
        defaultId = settingId + "-default-button";

        document.getElementById(settingId).oninput = function() {
          changeColorInput(settingId, "picker");
        };

        document.getElementById(textId).oninput = function() {
          changeColorInput(settingId, "text");
        };

        document.getElementById(defaultId).onclick = function() {
          changeColorInput(settingId, "default");
        };
    }
  });

  // Header image
  document.getElementById("header_image").onchange = function() {
    changeImage(this);
    disableInputs();
  };

  if (document.getElementById("header_image-delete")) {
    document.getElementById("header_image-delete-button").onclick = function() {
      document.getElementById("header_image-preview").style.display = "none";
      document.getElementById("header_image-delete").style.display  = "none";

      if (document.getElementById("header_image-retina")) {
        document.getElementById("header_image-retina").style.display = "none";
      }

      setStyle(["header_image2x", "header_image_position", "header_image_description"], "none");
      setStyle("header_image", "block");

      document.getElementById("header_image").value               = "";
      document.getElementById("header_image_description").value   = "";
      document.getElementById("header_image-delete-action").value = "true";
    };
  }

  // Reset button warning
  const buttonReset = document.getElementById("odyssey-reset");

  if (buttonReset) {
    buttonReset.addEventListener("click", function(event) {
      if (!confirm(document.getElementById("reset_warning").value)) {
        event.preventDefault();
      }
    });
  }

  // Restore from config file warning
  var configFilesRestore = document.getElementsByClassName("odyssey-backups-restore");

  for (var i = 0; i < configFilesRestore.length; i++) {
      configFilesRestore[i].addEventListener("click", function(event) {
        if (!confirm(document.getElementById("config_restore_warning").value)) {
          event.preventDefault();
        }
    });
  }

  // Remove config files warning
  const buttonConfigRemoveAll = document.getElementById("odyssey-backups-remove-all");

  if (buttonConfigRemoveAll) {
    buttonConfigRemoveAll.addEventListener("click", function(event) {
      if (!confirm(document.getElementById("config_remove_all_warning").value)) {
        event.preventDefault();
      }
    });
  }

  var configFilesRemove = document.getElementsByClassName("odyssey-backups-remove");

  for (var i = 0; i < configFilesRemove.length; i++) {
      configFilesRemove[i].addEventListener("click", function(event) {
        if (!confirm(document.getElementById("config_remove_warning").value)) {
          event.preventDefault();
        }
    });
  }
};
