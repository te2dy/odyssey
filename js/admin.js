/**
 * Shows or hides settings depending on others.
 */
function disableInputs() {
  if (document.getElementById("global_color_primary").value === "custom") {
    document.getElementById("global_color_primary_custom-input").style.display                = "block";
    document.getElementById("global_color_primary_amplified_custom-input").style.display      = "block";
    document.getElementById("global_color_primary_dark_custom-input").style.display           = "block";
    document.getElementById("global_color_primary_dark_amplified_custom-input").style.display = "block";
  } else {
    document.getElementById("global_color_primary_custom-input").style.display                = "none";
    document.getElementById("global_color_primary_amplified_custom-input").style.display      = "none";
    document.getElementById("global_color_primary_dark_custom-input").style.display           = "none";
    document.getElementById("global_color_primary_dark_amplified_custom-input").style.display = "none";
  }

  if (document.getElementById("header_image").value !== "") {
    document.getElementById("header_image_position-input").style.display          = "block";
    document.getElementById("header_image_description-input").style.display       = "block";
    document.getElementById("header_image_description-description").style.display = "block";
  } else {
    document.getElementById("header_image_position-input").style.display          = "none";
    document.getElementById("header_image_description-input").style.display       = "none";
    document.getElementById("header_image_description-description").style.display = "none";
  }

  if (document.getElementById("content_postlist_type").value !== "excerpt") {
    document.getElementById("content_postlist_thumbnail-input").style.display       = "block";
    document.getElementById("content_postlist_thumbnail-description").style.display = "block";
  } else {
    document.getElementById("content_postlist_thumbnail-input").style.display       = "none";
    document.getElementById("content_postlist_thumbnail-description").style.display = "none";
  }

  if (!document.getElementById("footer_enabled").checked) {
    document.getElementById("footer_credits-input").style.display                = "none";
    document.getElementById("footer_credits-description").style.display          = "none";
    document.getElementById("section-footer-social").style.display               = "none";
    document.getElementById("footer_social_diaspora-input").style.display        = "none";
    document.getElementById("footer_social_facebook-input").style.display        = "none";
    document.getElementById("footer_social_instagram-input").style.display       = "none";
    document.getElementById("footer_social_mastodon-input").style.display        = "none";
    document.getElementById("footer_social_signal-input").style.display          = "none";
    document.getElementById("footer_social_youtube-input").style.display         = "none";
    document.getElementById("footer_social_whatsapp-input").style.display        = "none";
    document.getElementById("footer_social_x-input").style.display               = "none";
    document.getElementById("footer_social_other-input").style.display           = "none";
    document.getElementById("footer_social_diaspora-description").style.display  = "none";
    document.getElementById("footer_social_facebook-description").style.display  = "none";
    document.getElementById("footer_social_instagram-description").style.display = "none";
    document.getElementById("footer_social_mastodon-description").style.display  = "none";
    document.getElementById("footer_social_signal-description").style.display    = "none";
    document.getElementById("footer_social_youtube-description").style.display   = "none";
    document.getElementById("footer_social_whatsapp-description").style.display  = "none";
    document.getElementById("footer_social_x-description").style.display         = "none";
    document.getElementById("footer_social_other-description").style.display     = "none";
  } else {
    document.getElementById("footer_credits-input").style.display                = "block";
    document.getElementById("footer_credits-description").style.display          = "block";
    document.getElementById("section-footer-social").style.display               = "block";
    document.getElementById("footer_social_diaspora-input").style.display        = "block";
    document.getElementById("footer_social_facebook-input").style.display        = "block";
    document.getElementById("footer_social_instagram-input").style.display       = "block";
    document.getElementById("footer_social_mastodon-input").style.display        = "block";
    document.getElementById("footer_social_signal-input").style.display          = "block";
    document.getElementById("footer_social_youtube-input").style.display         = "block";
    document.getElementById("footer_social_whatsapp-input").style.display        = "block";
    document.getElementById("footer_social_x-input").style.display               = "block";
    document.getElementById("footer_social_other-input").style.display           = "block";
    document.getElementById("footer_social_other-description").style.display     = "block";
    document.getElementById("footer_social_diaspora-description").style.display  = "block";
    document.getElementById("footer_social_facebook-description").style.display  = "block";
    document.getElementById("footer_social_instagram-description").style.display = "block";
    document.getElementById("footer_social_mastodon-description").style.display  = "block";
    document.getElementById("footer_social_signal-description").style.display    = "block";
    document.getElementById("footer_social_youtube-description").style.display   = "block";
    document.getElementById("footer_social_whatsapp-description").style.display  = "block";
    document.getElementById("footer_social_x-description").style.display         = "block";
    document.getElementById("footer_social_other-description").style.display     = "block";
  }
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
    var pageWidthUnitNew = document.getElementById("global_unit").value;

    if (pageWidthUnitNew === "px") {
      var pageWidthValueNew = parseInt(document.getElementById("global_page_width_value").value, 10) * 16;

      document.getElementById("global_page_width_value").value = pageWidthValueNew.toString();
    } else if (pageWidthUnitNew === 'em') {
      var pageWidthValueNew = parseInt(Number(document.getElementById("global_page_width_value").value) / 16, 10);

      document.getElementById("global_page_width_value").value = pageWidthValueNew.toString();
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
    var image = new Image();

    image.src = url;

    if (!image.complete || image.height === 0) {
      return false;
    } else {
      return true;
    }

    image.reset();
}

/**
 * Displays the image with the URL typed by the user.
 */
function changeImage() {
  if (imageExists(document.getElementById("header_image").value) === true) {
    let img = document.getElementById("header_image").value;

    document.getElementById("header_image-src").removeAttribute("style");
    document.getElementById("header_image-src").setAttribute("src", encodeURI(img));

    if (document.getElementById("header_image_position-retina") && document.getElementById("header_image-url").value !== img) {
      document.getElementById("header_image-retina").style.display = "none";
    }

    document.getElementById("header_image_position-input").style.display = "block";
    document.getElementById("header_image_description-input").style.display = "block";
    document.getElementById("header_image_description-description").style.display = "block";

    let imgExt = img.split('.').pop(),
        imgExtLength = imgExt.length,
        img2x = img.substring(0, img.length - (imgExtLength + 1)) + "-2x." + imgExt;

    if (imageExists(img2x) === true) {
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
    document.getElementById("header_image_position-input").style.display = "none";
    document.getElementById("header_image_description-input").style.display = "none";
    document.getElementById("header_image_description-description").style.display = "none";

    if (document.getElementById("header_image-retina")) {
      document.getElementById("header_image-retina").style.display = "none";
    }
  }
}

function fontsPreview() {
  var fonts = {
    "sans-serif": "system-ui, sans-serif",
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
  let colorPicker  = document.getElementById(settingId + "-picker").value,
      colorInput   = document.getElementById(settingId).value;
      colorDefault = document.getElementById(settingId + "-default-value").value;

  if (colorPicker !== colorInput) {
    if (context === "picker") {
      document.getElementById(settingId).value = colorPicker;
    } else if (context === "field") {
      document.getElementById(settingId + "-picker").value = colorInput;
    }
  } else if (context === "default") {
    document.getElementById(settingId + "-picker").value = colorDefault;
    document.getElementById(settingId).value             = colorDefault;
  }
}

window.onload = function() {
  disableInputs();
  changeImage();
  fontsPreview();

  window.onchange = function() {
    disableInputs();
    fontsPreview();
  };

  var pageWidthUnitDefault = document.getElementById("global_unit").value,
      pageWidthValueDefault = document.getElementById("global_page_width_value").value;

  document.getElementById("global_unit").onchange = function() {
    updatePageWidthSetting(pageWidthUnitDefault, pageWidthValueDefault);
    inputValidation();
  }

  window.oninput = function() {
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
    var settingId = colorSetting.firstElementChild.getAttribute("for"),
        pickerId  = settingId + "-picker";
        defaultId = settingId + "-default-button";

    document.getElementById(pickerId).oninput = function() {
      changeColorInput(settingId, "picker");
    };

    document.getElementById(settingId).oninput = function() {
      changeColorInput(settingId, "field");
    };

    document.getElementById(defaultId).onclick = function() {
      changeColorInput(settingId, "default");
    };
  });

  document.getElementById("header_image").onchange = function() {
    disableInputs();
    changeImage();
  };

  document.getElementById("header_image").oncut = function() {
    changeImage();
  };

  document.getElementById("header_image").onpaste = function() {
    changeImage();
  };

  document.getElementById("header_image").addEventListener("input", (e) => {
    let searchtimer;

    clearTimeout(searchtimer);

    searchtimer = setTimeout(() => {
      changeImage();
    }, 1000);
  });
};
