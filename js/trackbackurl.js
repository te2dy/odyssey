/*
 * Copies the trackback URL on click.
 *
 * Note:
 * This file is not directly loaded. Its content has been minimized
 * and integrated into the _comments.html template.
 */

window.onload = function(){
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
