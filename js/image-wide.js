/**
 * Enables wide images in posts.
 *
 * Note:
 * This file is not directly loaded. Its content has been minimized
 * and integrated into the _public.php file.
 */

window.addEventListener("load", imageWide);
window.addEventListener("resize", imageWide);

function getMeta(url, callback) {
    var img     = new Image();
        img.src = url;

    img.addEventListener("load", function() {
        callback(this.width, this.height);
    });
}

function imageWide() {
  var pageWidthEm    = parseInt(<?php echo $page_width_em; ?>), // Yes, there is PHP.
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

  const widthMatch = window.getComputedStyle(element).getPropertyValue('width').match(/\d+/);

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
