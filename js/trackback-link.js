/**
 * Copies the trackback URL on click.
 *
 * Note:
 * This file is not directly loaded. Its content has been minimized
 * and integrated into the _comments.html template.
 */

function origineTrackbackURLCopy() {
  // Gets the trackback button & URL.
  var trackbackButton = document.getElementById("trackback-url"),
      trackbackURL    = trackbackButton.getAttribute("data-trackback-url");

  // If the URL has already been copied, removes the Copied message.
  if (document.getElementById("trackback-url-copied")) {
    document.getElementById("trackback-url-copied").remove();
  }

  // Copies the trackback URL in the clipboard.
  navigator.clipboard.writeText(trackbackURL);

  // Displays a message to confirm the copy.
  trackbackButton.insertAdjacentHTML("afterend", ' <span id=trackback-url-copied>{{tpl:lang reactions-trackback-url-copied}}</span>');
}
