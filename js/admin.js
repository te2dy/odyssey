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
  return ['diaspora', 'facebook', 'instagram', 'mastodon', 'phone', 'signal', 'sms', 'youtube', 'whatsapp', 'x', 'other'];
}

// Toggles footer social settings.
function toggleFooterSocialSetting(id) {
  if (document.getElementById("social_" + id).value !== "" && document.getElementById("footer_enabled").checked) {
    if (document.getElementById("footer_enabled").checked) {
      setStyle("footer_social_" + id, "block");
    }

    if (document.getElementById("reactions_other").value !== "disabled") {
      setStyle("reactions_other_" + id, "block");
    } else {
      setStyle("reactions_other_" + id, "none");
    }
  } else {
    setStyle(
      [
        "footer_social_" + id,
        "reactions_other_" + id
      ],
      "none"
    );
  }
}

// Shows or hides settings depending on others.
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

    toggleFooterSocialSetting(siteId);
  }

  if (displayFooterSocialTitle === true) {
    document.getElementById("section-footer-social").style.display = "block";
  } else {
    document.getElementById("section-footer-social").style.display = "none";
  }
}

/**
 * Updates the page width output from the page width input.
 */
function pageWidthOutput(pageWidthUnitDefault, pageWidthValueDefault) {
  let pageWidthUnit  = pageWidthUnitDefault,
      pageWidthValue = pageWidthValueDefault;

  if (document.getElementById("global_unit").value === "em") {
    pageWidthUnit = "em";
  } else if (document.getElementById("global_unit").value === "px") {
    pageWidthUnit = "px";
  }

  if (document.getElementById("global_page_width_value").value) {
    pageWidthValue = document.getElementById("global_page_width_value").value;
  }

  document.getElementById("global_page_width_value-output-value").innerHTML = pageWidthValue;
}

/**
 * Updates page width settings depending on its values.
 */
function updatePageWidthSetting(pageWidthUnitDefault, pageWidthValueDefault) {
  // Updates the placeholder of the width value.
  if (document.getElementById("global_unit").value === "em") {
    document.getElementById("global_page_width_value").placeholder = document.getElementById("page_width_em_default").value;
  } else if (document.getElementById("global_unit").value === "px") {
    document.getElementById("global_page_width_value").placeholder = document.getElementById("page_width_px_default").value;
  }

  // Converts the page width value when the unit is changed.
  if (document.getElementById("global_page_width_value").value) {
    var pageWidthUnitNew  = document.getElementById("global_unit").value
        pageWidthValueNew = null;

    if (pageWidthUnitNew === "px") {
      pageWidthValueNew = parseInt(document.getElementById("global_page_width_value").value, 10) * 16;

      document.getElementById("global_page_width_value").setAttribute("min", "480");
      document.getElementById("global_page_width_value").setAttribute("max", "1280");
      document.getElementById("global_page_width_value").setAttribute("step", "2");
      document.getElementById("global_page_width_value").value = pageWidthValueNew.toString();

      document.getElementById("global_page_width_value-output-value").innerHTML = pageWidthValueNew.toString();
      document.getElementById("global_page_width_value-output-unit").innerHTML  = "px";
    } else if (pageWidthUnitNew === 'em') {
      pageWidthValueNew = parseInt(Number(document.getElementById("global_page_width_value").value) / 16, 10);

      document.getElementById("global_page_width_value").setAttribute("min", "30");
      document.getElementById("global_page_width_value").setAttribute("max", "80");
      document.getElementById("global_page_width_value").setAttribute("step", "1");
      document.getElementById("global_page_width_value").value = pageWidthValueNew.toString();

      document.getElementById("global_page_width_value-output-value").innerHTML = pageWidthValueNew.toString();
      document.getElementById("global_page_width_value-output-unit").innerHTML  = "em";
    }
  }
}

/**
 * Displays an error message if a value is incorrect.
 */
function inputValidation() {
  // Global page width setting.
  var getPageWidthValue = document.getElementById("global_page_width_value").value,
      pageWidth         = Number(getPageWidthValue);

  if (getPageWidthValue) {
    if (document.getElementById("global_unit").value === "em") {
      if (isNaN(pageWidth) || pageWidth < 30 || pageWidth > 80) {
        document.getElementById("global_page_width_value").classList.add("odyssey-value-error");
      } else {
        document.getElementById("global_page_width_value").classList.remove("odyssey-value-error")
      }
    } else {
      if (isNaN(pageWidth) || pageWidth < 480 || pageWidth > 1280) {
        document.getElementById("global_page_width_value").classList.add("odyssey-value-error");
      } else {
        document.getElementById("global_page_width_value").classList.remove("odyssey-value-error")
      }
    }
  } else {
    document.getElementById("global_page_width_value").classList.remove("odyssey-value-error")
  }
}

/**
 * Checks if an image exists via its URL.
 *
 * @link https://stackoverflow.com/a/14651421
 */
function imageExists(url) {
    let imgExist = {
      src: false,
      src2x: false
    };

    let image = new Image();

    image.src = url;

    if (image.complete && image.height > 0) {
      imgExist.src = true;
    }

    // Check Retina image then.
    image2x = new Image();

    let imgExt       = url.split('.').pop(),
        imgExtLength = imgExt.length;

    image2x.src = url.substring(0, url.length - (imgExtLength + 1)) + "-2x." + imgExt;

    if (image2x.complete && image2x.height > 0) {
      imgExist.src2x = true;
    }

    return imgExist;
}

/**
 * Displays the image with the URL typed by the user.
 */
function changeImage(inputImgURL) {
  if (inputImgURL !== "") {
    let imgExist = imageExists(inputImgURL);

    if (imgExist.src === true) {
      setStyle(
        [
          "header_image_position",
          "header_image_description"
        ],
        "block"
      );

      document.getElementById("header_image-src").removeAttribute("style");
      document.getElementById("header_image-src").setAttribute("src", encodeURI(inputImgURL));

      if (document.getElementById("header_image_position-retina") && document.getElementById("header_image-url").value !== inputImgURL) {
        document.getElementById("header_image-retina").style.display = "none";
      }

      if (imgExist.src2x === true) {
        if (document.getElementById("header_image-retina")) {
          document.getElementById("header_image-retina").style.display = "block";
        } else {
          var retinaNotice = document.createElement('p');
          retinaNotice.setAttribute("id", "header_image-retina");
          retinaNotice.innerText = document.getElementById("header_image-retina-text").value;

          var retinaNoticeElementAfter = document.getElementById("header_image-url");
          retinaNoticeElementAfter.parentNode.insertBefore(retinaNotice, retinaNoticeElementAfter);
        }
      }
    } else {
      document.getElementById("header_image-src").style.display = "none";

      setStyle(
        [
          "header_image-src",
          "header_image_position",
          "header_image_description"
        ],
        "none"
      );

      if (document.getElementById("header_image-retina")) {
        document.getElementById("header_image-retina").style.display = "none";
      }
    }
  } else {
    document.getElementById("header_image-src").style.display    = "none";

    setStyle(
      [
        "header_image_position",
        "header_image_description"
      ],
      "none"
    );

    if (document.getElementById("header_image-retina")) {
      document.getElementById("header_image-retina").style.display = "none";
    }
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

window.onload = function() {
  let inputImgURL = "";

  inputImgURL = document.getElementById("header_image").value;

  disableInputs();
  changeImage(inputImgURL);
  fontsPreview();

  window.onchange = function() {
    disableInputs();
    fontsPreview();
  };

  var pageWidthUnitDefault  = document.getElementById("global_unit").value,
      pageWidthValueDefault = document.getElementById("global_page_width_value").value;

  document.getElementById("global_page_width_value").oninput = function() {
    pageWidthOutput(pageWidthUnitDefault, pageWidthValueDefault);
  }

  document.getElementById("global_unit").onchange = function() {
    updatePageWidthSetting(pageWidthUnitDefault, pageWidthValueDefault);
    inputValidation();
  }

  window.oninput = function() {
    disableInputs();
    inputValidation();
  };

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

  document.getElementById("header_image").onchange = function() {
    inputImgURL = document.getElementById("header_image").value;

    changeImage(inputImgURL);
  };

  document.getElementById("header_image").oncut = function() {
    inputImgURL = document.getElementById("header_image").value;

    changeImage(inputImgURL);
  };

  document.getElementById("header_image").onpaste = function() {
    inputImgURL = document.getElementById("header_image").value;

    changeImage(inputImgURL);
  };

  document.getElementById("header_image").oninput = function() {
    let searchtimer;
    clearTimeout(searchtimer);

    searchtimer = setTimeout(() => {
      inputImgURL = document.getElementById("header_image").value;

      changeImage(inputImgURL);
    }, 1000);
  };
};
