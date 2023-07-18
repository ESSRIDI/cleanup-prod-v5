$( document ).ready(function() {
  //#region Transition en douceur entre pages
  window.transitionToPage = function (href) {
    document.querySelector("body").style.opacity = 0;
    setTimeout(function () {
      window.location.href = href;
    }, 500);
  };
  //#endregion Transition en douceur entre pages

  const mobileMenu = document.getElementById("menu-burger");

  function openHideMenu() {
    var nav = document.getElementById("nav-for-mobile");
    if (nav.style.display === "block") {
      nav.style.display = "none";
    } else {
      nav.style.display = "block";
    }
  }

  mobileMenu.addEventListener("click", openHideMenu);

  // const websiteDomain = 'https://cleanup.ovh' ;
  //#region Disparaitre les alertes apres 3 secondes

  setTimeout(function () {
    $(".alert , .cleanup-error, .cleanup-success").fadeOut("slow");
  }, 3000);
  //#endregion Disparaitre les alertes apres 3 secondes

  //#region Ajout de plusieurs volumes sans rafraichir la page

  document.querySelector("body").style.opacity = 1;
  const addFormToCollection = (e) => {
    const collectionHolder = document.querySelector(
      "." + e.currentTarget.dataset.collectionHolderClass
    );

    const item = document.createElement("li");

    item.innerHTML = collectionHolder.dataset.prototype.replace(
      /__name__/g,
      collectionHolder.dataset.index
    );

    collectionHolder.appendChild(item);

    collectionHolder.dataset.index++;
  };

  const addVolumeFormDeleteLink = (item) => {
    const removeFormButton = document.createElement("button");
    removeFormButton.innerText = "Supprimer ce volume";
    removeFormButton.classList = "delete_item_link";
    item.append(removeFormButton);

    removeFormButton.addEventListener("click", (e) => {
      e.preventDefault();
      // remove the li for the Volume form
      item.remove();
    });
  };
  document.querySelectorAll(".add_item_link").forEach((btn) => {
    // btn.addEventListener("click", console.log('hello'));
    btn.addEventListener("click", addFormToCollection);
  });

  document.querySelectorAll("ul.volumes li").forEach((volume) => {
    addVolumeFormDeleteLink(volume);
  });
  //#endregion Ajout de plusieurs volumes sans rafraichir la page

  //#region Mettre a jour les volumes dans edit/new price form automatiquement
  $("#price_form_product").change(function () {
    // console.log('selected ....');
    const productSelector = $(this);

    // const productId=productSelector.id;
    // chercher la list des volumes du produit selectionné
    $.ajax({
      url: `https://cleanup.ovh/admin/users/price/get-list-volumes-of-product/${productSelector.val()}`,
      type: "GET",
      dataType: "JSON",
      // data: {
      //   productId: productSelector.val()
      // },

      success: function (volumes) {
        const volumeSelect = $("#price_form_volume");

        //Supprimer les valeurs qui existent déja
        volumeSelect.html("");

        // Valeur initial...
        volumeSelect.append(
          "<option value>" +
            productSelector.find("option:selected").text() +
            " ...</option>"
        );

        $.each(volumes, function (key, volume) {
          volumeSelect.append(
            '<option value="' + volume.id + '">' + volume.volume + "</option>"
          );
        });
      },
      error: function (err) {
        alert("Une erreur s'est produite veuillez réessayer plus tard");
      },
    });
  });
  //#endregion Mettre a jour les volumes dans edit/new price form automatiquement

  //#region Dans la page Show du produit : selectionner le volume donne le vrai prix par rapport client & volume
  $("#selected-volume-by-client").change(function () {
    let currentVolumeId = $(this).children("option:selected").val();
    let productId = $(this).children("option:selected").attr("data-product-id");
    //  console.log(productId);
    //  console.log(this);
    //  console.log(
    //  "volume selectionnée de la page show id dans tab  : ",currentVolumeId);
    $.ajax({
      url: `https://cleanup.ovh/user/get-client-price-for-selected-volume/${productId}/${currentVolumeId}`,

      type: "GET",
      dataType: "JSON",

      success: function (price) {
        const priceSpan = $("#price-client");
        priceSpan.html("€" + price);
      },
      error: function (err) {
        alert("Une erreur s'est produite veuillez réessayer plus tard");
      },
    });
  });
  //#endregion Dans la page Show du produit : selectionner le volume donne le vrai prix par rapport client & volume

  //#region Agree terms for registration
  const queryString = window.location.search;
  // console.log(queryString);
  const urlParams = new URLSearchParams(queryString);

  if (urlParams.has("accepted")) {
    document.getElementById("registration_form_agreeTerms").checked = true;
  }
  //#endregion Agree terms for registration

  //#region Reject / Accept user

  $("select[name='validate-user']").each(function () {
    $(this).change(function () {
      let selectedDecision = $(this).children("option:selected").val();
      // console.log(selectedDecision);
      const idUser = $(this).attr("data-user-id");

      // chercher la list des volumes du produit selectionné
      $.ajax({
        url: `https://cleanup.ovh/admin/users/validate/${idUser}/${selectedDecision}`,

        type: "POST",
        dataType: "JSON",
        // data: {
        //   price: $(this).attr('data-price')

        // },

        success: function (response) {
          // $('td[name="total-row"]').each(function(orderLineQty){
          //   $(this).html('<td class="text-cleanup-valid" name="total-row" data-price={{item.price.price}} data-label="Total">'+ $(this).attr('data-price') * orderLineQty+"</td>");
          // })
        },
        error: function (err) {
          alert("Une erreur s'est produite veuillez réessayer plus tard");
        },
      });
    });
  });

  
  //#endregion Reject / Accept user

  function onSubmit(token) {
    document.getElementById("contact_form_submit").submit();
  }

  //#region Mettre a jour les quantité dans le panier
  $(".cart-quantity").each(function () {
    // console.log('selected ....');

    $(this).change(function () {
      const orderLineQty = $(this).val();
      const orderLineId = $(this).attr("data-card-line-id");
      const price = $(this).attr("data-price");
      //  console.log(price);
      // $('td[name="total-row"]').html(orderLineQty * price);

      $.ajax({
        url:
          `https://cleanup.ovh/user/shopping/cart/modify/` +
          $(this).attr("data-card-line-id") +
          "/" +
          $(this).val(),

        type: "POST",
        dataType: "JSON",
        // data: {
        //   price: $(this).attr('data-price')

        // },

        success: function (response) {
          // $('td[name="total-row"]').each(function(orderLineQty){
          //   $(this).html('<td class="text-cleanup-valid" name="total-row" data-price={{item.price.price}} data-label="Total">'+ $(this).attr('data-price') * orderLineQty+"</td>");
          // })
        },
        error: function (err) {
          alert("Une erreur s'est produite veuillez réessayer plus tard");
        },
      });
    });
  });
  //#endregion Mettre a jour les quantité dans le panier
  //#region Mettre a jour le prix dans une ligne de panier
  $(".tr-cart").each(function () {
    $(this).change(function () {
      let totalRow =
        $(this).attr("data-price") *
        $(this)
          .children('td[name="cart-quantity"]')
          .children('input[name="cart-quantity"]')
          .val();

      $(this)
        .children('td[name="total-row"]')
        .html(
          '<td class="text-cleanup-valid" name="total-row" data-label="Total">&nbsp;&nbsp;&nbsp;€' +
            totalRow.toFixed(2) +
            "</td>"
        );
    });
  });
  //#endregion Mettre a jour le prix dans une ligne de panier


  //#region Mettre a jour le prix total de panier
  
 
  $("#cart-table").change(function () {

let totaux =0 ;
$(this).children(".tr-cart").each(function(){

  let myArr= $(this).children('td[name="total-row"]').text().split('€');
  console.log(myArr);
  totaux += parseFloat(myArr[1])
})
//
$('td[name="amount-cart"]').html(
  "&nbsp;&nbsp;&nbsp;€" + totaux.toFixed(2) + "<sup>*</sup>"
);
  });


  //#endregion Mettre a jour le prix total de panier


  //#region order tracking status

  $("select[name='order-status-select']").each(function () {
    $(this).change(function () {
      let status = $(this).children("option:selected").val();
  
      const idOrder = $(this).attr("data-order-id");

      
      $.ajax({
        url: `https://cleanup.ovh/admin/track/${idOrder}/${status}`,

        type: "POST",
        dataType: "JSON",
   
        success: function (response) {
   console.log(response);
        },
        error: function (err) {
          alert("Une erreur s'est produite veuillez réessayer plus tard");
        },
      });
    });
  });

  
  //#endregion order tracking status


  //#region carousel
  let slideIndex = 0;
  showSlides();

  function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");

    for (i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) {
      slideIndex = 1;
    }

    slides[(slideIndex - 1)].style.display = "block";

    setTimeout(showSlides, 3000); // Change image every 3 seconds
  }
  //#endregion carousel
});
