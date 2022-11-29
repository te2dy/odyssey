/**
 * Enables or disables the submit button of the search form.
 *
 * Note:
 * This file is not directly loaded. Its content has been minimized
 * and integrated into the _searchform.html template.
 */

window.onload = function(){
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
}
