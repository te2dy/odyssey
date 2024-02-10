/**
 * Shows or hides settings depending on others.
 */
function disableInputs() {
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
    document.getElementById("footer_credits-input").style.display               = "none";
    document.getElementById("footer_credits-description").style.display         = "none";
    /*
    document.getElementById("section-footer-social-links").style.display        = "none";
    document.getElementById("footer_social_links_diaspora-input").style.display = "none";
    document.getElementById("footer_social_links_discord-input").style.display  = "none";
    document.getElementById("footer_social_links_facebook-input").style.display = "none";
    document.getElementById("footer_social_links_github-input").style.display   = "none";
    document.getElementById("footer_social_links_mastodon-input").style.display = "none";
    document.getElementById("footer_social_links_signal-input").style.display   = "none";
    document.getElementById("footer_social_links_tiktok-input").style.display   = "none";
    document.getElementById("footer_social_links_whatsapp-input").style.display = "none";
    document.getElementById("footer_social_links_x-input").style.display        = "none";
    */
  } else {
    document.getElementById("footer_credits-input").style.display               = "block";
    document.getElementById("footer_credits-description").style.display         = "block";
    /*
    document.getElementById("section-footer-social-links").style.display        = "block";
    document.getElementById("footer_social_links_diaspora-input").style.display = "block";
    document.getElementById("footer_social_links_discord-input").style.display  = "block";
    document.getElementById("footer_social_links_facebook-input").style.display = "block";
    document.getElementById("footer_social_links_github-input").style.display   = "block";
    document.getElementById("footer_social_links_mastodon-input").style.display = "block";
    document.getElementById("footer_social_links_signal-input").style.display   = "block";
    document.getElementById("footer_social_links_tiktok-input").style.display   = "block";
    document.getElementById("footer_social_links_whatsapp-input").style.display = "block";
    document.getElementById("footer_social_links_x-input").style.display        = "block";
    */
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
    document.getElementById("header_image-src").removeAttribute("style");
    document.getElementById("header_image-src").setAttribute("src", encodeURI(document.getElementById("header_image").value));

    if (document.getElementById("header_image_position-retina") && document.getElementById("header_image-url").value !== document.getElementById("header_image").value) {
      document.getElementById("header_image-retina").style.display = "none";
    }

    document.getElementById("header_image_position-input").style.display = "block";
    document.getElementById("header_image_description-input").style.display = "block";
    document.getElementById("header_image_description-description").style.display = "block";
  } else {
    document.getElementById("header_image-src").style.display = "none";
    document.getElementById("header_image_position-input").style.display = "none";
    document.getElementById("header_image_description-input").style.display = "none";
    document.getElementById("header_image_description-description").style.display = "none";
  }
}

function fontsPreview() {
  if (document.getElementById("content_text_font").value === 'same') {
    document.getElementById("odyssey-config-content-font-preview").style.display = "none";
  } else {
    document.getElementById("odyssey-config-content-font-preview").style.display = "block";
  }

  if (document.getElementById("global_font_family").value === "sans-serif") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "system-ui, sans-serif";
  } else if (document.getElementById("global_font_family").value === "transitional") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Charter, \"Bitstream Charter\", \"Sitka Text\", Cambria, serif";
  } else if (document.getElementById("global_font_family").value === "old-style") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "\"Iowan Old Style\", \"Palatino Linotype\", \"URW Palladio L\", P052, serif";
  } else if (document.getElementById("global_font_family").value === "humanist") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Seravek, \"Gill Sans Nova\", Ubuntu, Calibri, \"DejaVu Sans\", source-sans-pro, sans-serif";
  } else if (document.getElementById("global_font_family").value === "geometric-humanist") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Avenir, Montserrat, Corbel, \"URW Gothic\", source-sans-pro, sans-serif";
  } else if (document.getElementById("global_font_family").value === "classical-humanist") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Optima, Candara, \"Noto Sans\", source-sans-pro, sans-serif";
  } else if (document.getElementById("global_font_family").value === "neo-grotesque") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Inter, Roboto, \"Helvetica Neue\", \"Arial Nova\", \"Nimbus Sans\", Arial, sans-serif";
  } else if (document.getElementById("global_font_family").value === "monospace-slab-serif") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "\"Nimbus Mono PS\", \"Courier New\", monospace";

  } else if (document.getElementById("global_font_family").value === "monospace-code") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "ui-monospace, \"Cascadia Code\", \"Source Code Pro\", Menlo, Consolas, \"DejaVu Sans Mono\", monospace";
  } else if (document.getElementById("global_font_family").value === "industrial") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Bahnschrift, \"DIN Alternate\", \"Franklin Gothic Medium\", \"Nimbus Sans Narrow\", sans-serif-condensed, sans-serif";
  } else if (document.getElementById("global_font_family").value === "rounded-sans") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "ui-rounded, \"Hiragino Maru Gothic ProN\", Quicksand, Comfortaa, Manjari, \"Arial Rounded MT\", \"Arial Rounded MT Bold\", Calibri, source-sans-pro, sans-serif";
  } else if (document.getElementById("global_font_family").value === "slab-serif") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Rockwell, \"Rockwell Nova\", \"Roboto Slab\", \"DejaVu Serif\", \"Sitka Small\", serif";

  } else if (document.getElementById("global_font_family").value === "antique") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Superclarendon, \"Bookman Old Style\", \"URW Bookman\", \"URW Bookman L\", \"Georgia Pro\", Georgia, serif";
  } else if (document.getElementById("global_font_family").value === "didone") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "Didot, \"Bodoni MT\", \"Noto Serif Display\", \"URW Palladio L\", P052, Sylfaen, serif";
  } else if (document.getElementById("global_font_family").value === "handwritten") {
    document.getElementById("odyssey-config-global-font-preview").style.fontFamily = "\"Segoe Print\", \"Bradley Hand\", Chilanka, TSCu_Comic, casual, cursive";
  }

  if (document.getElementById("content_text_font").value === "sans-serif") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "system-ui, sans-serif";
  } else if (document.getElementById("content_text_font").value === "transitional") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Charter, \"Bitstream Charter\", \"Sitka Text\", Cambria, serif";
  } else if (document.getElementById("content_text_font").value === "old-style") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "\"Iowan Old Style\", \"Palatino Linotype\", \"URW Palladio L\", P052, serif";
  } else if (document.getElementById("content_text_font").value === "humanist") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Seravek, \"Gill Sans Nova\", Ubuntu, Calibri, \"DejaVu Sans\", source-sans-pro, sans-serif";
  } else if (document.getElementById("content_text_font").value === "geometric-humanist") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Avenir, Montserrat, Corbel, \"URW Gothic\", source-sans-pro, sans-serif";
  } else if (document.getElementById("content_text_font").value === "classical-humanist") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Optima, Candara, \"Noto Sans\", source-sans-pro, sans-serif";
  } else if (document.getElementById("content_text_font").value === "neo-grotesque") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Inter, Roboto, \"Helvetica Neue\", \"Arial Nova\", \"Nimbus Sans\", Arial, sans-serif";
  } else if (document.getElementById("content_text_font").value === "monospace-slab-serif") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "\"Nimbus Mono PS\", \"Courier New\", monospace";

  } else if (document.getElementById("content_text_font").value === "monospace-code") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "ui-monospace, \"Cascadia Code\", \"Source Code Pro\", Menlo, Consolas, \"DejaVu Sans Mono\", monospace";
  } else if (document.getElementById("content_text_font").value === "industrial") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Bahnschrift, \"DIN Alternate\", \"Franklin Gothic Medium\", \"Nimbus Sans Narrow\", sans-serif-condensed, sans-serif";
  } else if (document.getElementById("content_text_font").value === "rounded-sans") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "ui-rounded, \"Hiragino Maru Gothic ProN\", Quicksand, Comfortaa, Manjari, \"Arial Rounded MT\", \"Arial Rounded MT Bold\", Calibri, source-sans-pro, sans-serif";
  } else if (document.getElementById("content_text_font").value === "slab-serif") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Rockwell, \"Rockwell Nova\", \"Roboto Slab\", \"DejaVu Serif\", \"Sitka Small\", serif";

  } else if (document.getElementById("content_text_font").value === "antique") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Superclarendon, \"Bookman Old Style\", \"URW Bookman\", \"URW Bookman L\", \"Georgia Pro\", Georgia, serif";
  } else if (document.getElementById("content_text_font").value === "didone") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "Didot, \"Bodoni MT\", \"Noto Serif Display\", \"URW Palladio L\", P052, Sylfaen, serif";
  } else if (document.getElementById("content_text_font").value === "handwritten") {
    document.getElementById("odyssey-config-content-font-preview").style.fontFamily = "\"Segoe Print\", \"Bradley Hand\", Chilanka, TSCu_Comic, casual, cursive";
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
    // inputChange();
  };

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
