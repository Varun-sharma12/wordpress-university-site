//This file works for the like professor functionality
import $ from "jquery";
class Like {
  constructor() {
    this.events();
  }
  //All the events will be here.
  events() {
    //Fire Event on click
    $(".like-box").on("click", this.ourClickDispatcher.bind(this));
  }

  ourClickDispatcher(e) {
    //Get only the closest element containing like-box class where we click.
    var currentLikeBox = $(e.target).closest(".like-box");
    console.log("sdfsd54423", currentLikeBox);
    if (currentLikeBox.attr("data-exists") == "yes") {
      this.deleteLike(currentLikeBox);
    } else {
      this.createLike(currentLikeBox);
    }
  }
  //CreatLike function with Fetch instead of ajax.
  //   createLike(currentLikeBox) {
  //     console.log(currentLikeBox)
  //     const data = {
  //         'professorId' : currentLikeBox.data('professor'),
  //     };
  //     console.log(data)
  //     fetch(`${universityData.root_url}/wp-json/university/v1/manageLike`, {
  //         headers: {
  //             'Content-Type' : 'application/json'
  //         },
  //         credentials: 'same-origin',
  //         method: 'POST',
  //         body: JSON.stringify(data),
  //     }).then(function(response){
  //         return response.json();
  //     }).then (response => {
  //         console.log(response);
  //     }).catch(err => console.log(`error : ${err}`))
  // }

  createLike(currentLikeBox) {
    // console.log(universityData.nonce)
    //Ajax call on the custom rest api end point to create a like post with professor id saved in custom field
    $.ajax({
      beforeSend: (xhr) => {
        xhr.setRequestHeader("X-WP-Nonce", universityData.nonce);
      },
      url: universityData.root_url + "/wp-json/university/v1/manageLike",
      type: "POST",
      data: { professorID: currentLikeBox.data("professor") },
      success: (response) => {
        currentLikeBox.attr("data-exists", "yes");
        var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
        currentLikeBox.find(".like-count").html(likeCount + 1);
        currentLikeBox.attr("data-like", response);
        console.log("sdfsdf", response);
      },
      error: (response) => {
        console.log(response);
      },
    });
  }
  deleteLike(currentLikeBox) {
    //Ajax call on the custom rest api end point to delete a like post.
    $.ajax({
      beforeSend: (xhr) => {
        xhr.setRequestHeader("X-WP-Nonce", universityData.nonce);
      },
      url: universityData.root_url + "/wp-json/university/v1/manageLike",
      type: "DELETE",
      data: {like: currentLikeBox.attr("data-like") },
      success: (response) => {
        currentLikeBox.attr("data-exists", "no");
        var likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
        currentLikeBox.find(".like-count").html(likeCount - 1);
        currentLikeBox.attr("data-like", "");
        // console.log("sdfsdf",response)
        console.log(response);
      },
      error: (response) => {
        console.log(response);
      },
    });
  }
}
export default Like;
