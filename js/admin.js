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
  console.log("ok");

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

window.onload = function() {
  disableInputs();
  changeImage();

  window.onchange = function() {
    disableInputs();
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
