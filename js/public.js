window.onload = function(){
  /**
   * Enables or disables the submit button of the search form.
   *
   * Note:
   * This file is not directly loaded. Its content has been minimized
   * and integrated into the _searchform.html template.
   */

  if (document.getElementsByClassName("search-form-submit")[0]) {
    // Gets the search query if exists.
    var params      = (new URL(document.location)).searchParams,
        searchQuery = params.get("q");

    if (searchQuery !== "") {
      document.getElementsByClassName("search-form-submit")[0].disabled = true;
    }

    // On input change.
    document.getElementsByClassName("search-form")[0].oninput = function () {
      // If The input is empty or its value is equal to the search, disables the submit button.
      if (document.getElementsByClassName("search-form-field")[0].value && document.getElementsByClassName("search-form-field")[0].value !== searchQuery) {
        document.getElementsByClassName("search-form-submit")[0].disabled = false;
      } else {
        document.getElementsByClassName("search-form-submit")[0].disabled = true;
      }
    };
  }

  /*
   * Copies the trackback URL on click.
   *
   * Note:
   * This file is not directly loaded. Its content has been minimized
   * and integrated into the _comments.html template.
   */
  if (document.getElementById("trackback-url")) {
    document.getElementById("trackback-url").onclick = function() {
      var host         = window.location.protocol + "//" + window.location.host,
          trackbackURL = document.getElementById("trackback-url").innerHTML;

      // If trackbackURL is a valid URL, returns it.
      var url;

      try {
        url = new URL(trackbackURL).href;
      } catch (_) {
        return false;
      }

      // If the URL is valid.
      if (url.href !== false) {
        // Copies the trackback URL in the clipboard and show a message.
        navigator.clipboard.writeText(url).then(
          () => {
            document.getElementById("trackback-url-copied").style.display = "inline";
          },
          () => {
            document.getElementById("trackback-url-copied").style.display = "none";
          }
        );
      }
    };
  }
};

/**
 * Enables wide images in posts.
 *
 * Note:
 * This file is not directly loaded. Its content has been minimized
 * and integrated into the _public.php file.
 */
if (document.getElementById('script-theme').getAttribute('data-pagewidth') && document.getElementsByTagName("article")[0]) {
  window.addEventListener("load", imageWide);
  window.addEventListener("resize", imageWide);
}

function getMeta(url, callback) {
    var img     = new Image();
        img.src = url;

    img.addEventListener("load", function() {
        callback(this.width, this.height);
    });
}

function imageWide() {
  var pageWidthEm    = parseInt(document.getElementById('script-theme').getAttribute('data-pagewidth')),
      imgWideWidthEm = pageWidthEm + 10,
      pageWidthPx    = 0, // To set later.
      imgWideWidthPx = 0, // To set later.
      fontSizePx     = 0; // To set later.

  /**
   * Gets the font size defined by the browser.
   *
   * @link https://brokul.dev/detecting-the-default-browser-font-size-in-javascript
   */
  const element = document.createElement('div');

  element.style.width   = '1rem';
  element.style.display = 'none';

  document.body.append(element);

  var widthMatch = window.getComputedStyle(element).getPropertyValue('width').match(/\d+/);

  element.remove();

  // Sets the font size in px.
  if (widthMatch && widthMatch.length >= 1) {
      fontSizePx = parseInt(widthMatch[0]);
  }

  // If a font size is set, calculates page and image width in px.
  if (fontSizePx > 0) {
      pageWidthPx    = pageWidthEm * fontSizePx;
      imgWideWidthPx = imgWideWidthEm * fontSizePx;
  }

  // Gets all images of the post.
  var img = document.getElementsByTagName("article")[0].getElementsByTagName("img"),
      i   = 0;

  // Expands each image.
  while (i < img.length) {
      let myImg = img[i];

      getMeta(
          myImg.src,
          function(width, height) {
              let imgWidth   = width,
                  imgHeight  = height,
                  myImgStyle = "";

              // Applies expand styles only to lanscape images.
              if (imgWidth > pageWidthPx && imgWidth > imgHeight) {
                  if (imgWidth > imgWideWidthPx) {
                      imgHeight = parseInt(imgWideWidthPx * imgHeight / imgWidth);
                      imgWidth  = imgWideWidthPx;
                  }

                  // Defines the styles of the image.
                  myImgStyle = "display:block;margin-left:50%;transform:translateX(-50%);max-width:95vw;";

                  // Sets the image attributes.
                  myImg.setAttribute("style", myImgStyle);

                  if (imgWidth) {
                      myImg.setAttribute("width", imgWidth);
                  }

                  if (imgHeight) {
                      myImg.setAttribute("height", imgHeight);
                  }
              }
          }
      );

      i++;
  }
}
