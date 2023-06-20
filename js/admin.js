/**
 * Shows or hides settings depending on others.
 */
function disableInputs() {
  if (document.getElementById("global_color_primary").value === "gray") {
    document.getElementById("setting-linksunderline-recommended").style.display = "inline";
  } else {
    document.getElementById("setting-linksunderline-recommended").style.display = "none";
  }

  if (document.getElementById("global_js").checked || document.getElementById("content_images_wide").checked) {
    document.getElementById("originemini-message-js").style.display = "block";
  } else {
    document.getElementById("originemini-message-js").style.display = "none";
  }

  if (document.getElementById("global_js").checked) {
    document.getElementById("hash-searchform").style.display   = "block";
    document.getElementById("hash-trackbackurl").style.display = "block";
  } else {
    document.getElementById("hash-searchform").style.display   = "none";
    document.getElementById("hash-trackbackurl").style.display = "none";
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

  if (document.getElementById("content_post_list_type").value !== 'custom') {
    document.getElementById("content_post_list_custom-input").style.display       = "none";
    document.getElementById("content_post_list_custom-description").style.display = "none";
  } else {
    document.getElementById("content_post_list_custom-input").style.display       = "block";
    document.getElementById("content_post_list_custom-description").style.display = "block";
  }

  if (document.getElementById("content_images_wide").value !== 'disabled') {
    document.getElementById("content_images_wide_size-input").style.display        = "block";
    document.getElementById("content_images_wide_size-description").style.display  = "block";
    document.getElementById("content_image_custom_size-input").style.display       = "block";
    document.getElementById("content_image_custom_size-description").style.display = "block";
  } else {
    document.getElementById("content_images_wide_size-input").style.display        = "none";
    document.getElementById("content_images_wide_size-description").style.display  = "none";
    document.getElementById("content_image_custom_size-input").style.display       = "none";
    document.getElementById("content_image_custom_size-description").style.display = "none";
  }

  if (document.getElementById("content_post_list_time").checked || document.getElementById("content_post_time").checked) {
    document.getElementById("content_separator-input").style.display       = "block";
    document.getElementById("content_separator-description").style.display = "block";
    document.getElementById("section-content-other").style.display         = "block";
  } else {
    document.getElementById("content_separator-input").style.display       = "none";
    document.getElementById("content_separator-description").style.display = "none";
    document.getElementById("section-content-other").style.display         = "none";
  }

  if (document.getElementById("widgets_nav_position").value === "disabled") {
    document.getElementById("widgets_search_form-input").style.display       = "none";
    document.getElementById("widgets_search_form-description").style.display = "none";
  } else {
    document.getElementById("widgets_search_form-input").style.display       = "block";
    document.getElementById("widgets_search_form-description").style.display = "block";
  }

  if (!document.getElementById("footer_enabled").checked) {
    document.getElementById("footer_credits-input").style.display               = "none";
    document.getElementById("footer_credits-description").style.display         = "none";
    document.getElementById("section-footer-social-links").style.display        = "none";
    document.getElementById("footer_social_links_diaspora-input").style.display = "none";
    document.getElementById("footer_social_links_discord-input").style.display  = "none";
    document.getElementById("footer_social_links_facebook-input").style.display = "none";
    document.getElementById("footer_social_links_github-input").style.display   = "none";
    document.getElementById("footer_social_links_mastodon-input").style.display = "none";
    document.getElementById("footer_social_links_signal-input").style.display   = "none";
    document.getElementById("footer_social_links_tiktok-input").style.display   = "none";
    document.getElementById("footer_social_links_twitter-input").style.display  = "none";
    document.getElementById("footer_social_links_whatsapp-input").style.display = "none";
  } else {
    document.getElementById("footer_credits-input").style.display               = "block";
    document.getElementById("footer_credits-description").style.display         = "block";
    document.getElementById("section-footer-social-links").style.display        = "block";
    document.getElementById("footer_social_links_diaspora-input").style.display = "block";
    document.getElementById("footer_social_links_discord-input").style.display  = "block";
    document.getElementById("footer_social_links_facebook-input").style.display = "block";
    document.getElementById("footer_social_links_github-input").style.display   = "block";
    document.getElementById("footer_social_links_mastodon-input").style.display = "block";
    document.getElementById("footer_social_links_signal-input").style.display   = "block";
    document.getElementById("footer_social_links_tiktok-input").style.display   = "block";
    document.getElementById("footer_social_links_twitter-input").style.display  = "block";
    document.getElementById("footer_social_links_whatsapp-input").style.display = "block";
  }
}

/**
 * Updates page width settings depending on its values.
 */
function updatePageWidthSetting(pageWidthUnitDefault, pageWidthValueDefault) {
  // Updates the placeholder of the width value.
  if (document.getElementById("global_page_width_unit").value === "em") {
    document.getElementById("global_page_width_value").placeholder = document.getElementById("page_width_em_default").value;
  } else if (document.getElementById("global_page_width_unit").value === "px") {
    document.getElementById("global_page_width_value").placeholder = document.getElementById("page_width_px_default").value;
  }

  // Converts the page width value when the unit is changed.
  if (document.getElementById("global_page_width_value").value) {
    var pageWidthUnitNew = document.getElementById("global_page_width_unit").value;

    if (pageWidthUnitNew === 'px') {
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
function inputCheckMessage() {
  var newPageWidth = Number(document.getElementById("global_page_width_value").value);

  if (newPageWidth) {
    if (document.getElementById("global_page_width_unit").value === 'em') {
      if (isNaN(newPageWidth) || newPageWidth < 30 || newPageWidth > 80) {
        document.getElementById("global_page_width_value").classList.add("om-value-error");
      } else {
        document.getElementById("global_page_width_value").classList.remove("om-value-error")
      }
    } else {
      if (isNaN(newPageWidth) || newPageWidth < 480 || newPageWidth > 1280) {
        document.getElementById("global_page_width_value").classList.add("om-value-error");
      } else {
        document.getElementById("global_page_width_value").classList.remove("om-value-error")
      }
    }
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
    document.getElementById("header_image-retina").style.display = "none";
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

  var pageWidthUnitDefault = document.getElementById("global_page_width_unit").value,
      pageWidthValueDefault = document.getElementById("global_page_width_value").value;

  document.getElementById("global_page_width_unit").onchange = function() {
    updatePageWidthSetting(pageWidthUnitDefault, pageWidthValueDefault);
    inputCheckMessage();
  }

  window.oninput = function() {
    inputCheckMessage();
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
