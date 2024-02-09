import $ from "jquery";
class Search {
  //1. descrive and create/initiate our object
  constructor() {
    this.addSearchHTML();
    this.openButton = $(".js-search-trigger");
    this.closeButton = $(".search-overlay__close");
    this.searchOverlay = $(".search-overlay");
    this.searchField = $("#search-term");
    this.isOverlayOpen = false;
    this.typingTimer;
    this.resultDiv = $("#search-overlay__results");
    this.isSpinning = false;
    this.previousValue;
    this.events();
  }
  //2.events
  events() {
    this.openButton.on("click", this.openOverlay.bind(this));
    this.closeButton.on("click", this.closeOverlay.bind(this));
    $(document).on("keyup", this.keyPressDispatcher.bind(this));
    this.searchField.on("keyup", this.typingLogic.bind(this));
  }

  // 3. Methods(function, action...)
  typingLogic() {
    if (this.searchField.val() !== this.previousValue) {
      clearTimeout(this.typingTimer);
      if (this.searchField.val()) {
        if (!this.isSpinning) {
          this.resultDiv.html('<div class = "spinner-loader"></div>');
          this.isSpinning = true;
        }
        this.typingTimer = setTimeout(this.getResults.bind(this), 750);
      } else {
        this.resultDiv.html("");
        this.isSpinning = false;
      }
    }
    this.previousValue = this.searchField.val();
    //
  }

  getResults() {
    $.when(
      $.getJSON(`${universityData.root_url}/wp-json/wp/v2/posts?search=${this.searchField.val()}`),
        $.getJSON(`${universityData.root_url }/wp-json/wp/v2/pages?search=${this.searchField.val()}`)
    ).then((posts, pages) => {
      console.log(posts)
      var combinedResult = posts[0].concat(pages[0]);
      let html = combinedResult.length
        ? `<h2 className="search-overlay__section-title">General Information</h2>     
     <ul className="link-list min-list">
    ${combinedResult.map((post) => {
    return `<li><a href="${post.link}">${post.title.rendered}</a>${ post.type == 'post' ? ` by ${post.authorName} `: '' } </li>`;
                                    })
  .join("")}          
</ul>` : `<p>No content Found</p>`;
      this.resultDiv.html(html);
      this.isSpinning = false;
    }, () => this.resultDiv.html('<p>Unexpected Error Please try again later. </p>'));

    
  }

  keyPressDispatcher(e) {
    if (
      e.keyCode == 83 &&
      !this.isOverlayOpen &&
      $("input, textarea").is(":focus")
    )
      this.openOverlay();
    else if (e.keyCode === 27 && this.isOverlayOpen) this.closeOverlay();
  }
  openOverlay() {
    console.log(this.searchField);
    this.searchField.val("");
    this.resultDiv.html("");
    setTimeout(() => this.searchField.trigger("focus"), 1000);
    this.searchOverlay.addClass("search-overlay--active");
    $("body").addClass("body-no-scroll");
    this.isOverlayOpen = true;
  }
  closeOverlay() {
    this.searchOverlay.removeClass("search-overlay--active");
    $("body").removeClass("body-no-scroll");
    this.isOverlayOpen = false;
  }

  addSearchHTML() {
    const searchOverlay = `<div class="search-overlay">
    <div class="search-overlay__top">
      <div class="container">
        <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
        <input type="text" class="search-term" placeholder="What are you looking for?" id="search-term">
        <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
      </div>
    </div>
    <div class="container">
      <div id="search-overlay__results"></div>
    </div>
  </div>`;
    $("body").append(searchOverlay);
  }
}

export default Search;
