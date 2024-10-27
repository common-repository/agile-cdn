jQuery(document).ready(function () {
  // notification
  jQuery("#agile-cdn-tip-close").on("click", function () {
    jQuery("#agile-cdn-tip").fadeOut();
  });
  // dot
  jQuery("input").on("input", function () {
    if (!jQuery("#agliecdn-submit-botton-dot").hasClass("dot")) {
      jQuery("#agliecdn-submit-botton-dot").addClass("dot");
    }
  });
  // form submit
  jQuery("#agile-cdn-form").ajaxForm({
    beforeSubmit: function (data) {
      let flag = true;
      let labelMap = {
        agile_cdn_url: "Site URL",
        agile_cdn_prefix: "Static Files's CDN domain"
      };

      for (item of data) {
        if (item.name === "agile_cdn_prefix" || item.name === "agile_cdn_url") {
          if (!(String(item.value).startsWith("http://") || String(item.value).startsWith("https://"))) {
            jQuery("input[name=" + item.name + "]")
              .next()
              .html(labelMap[item.name] + " must start with http:// or https://");
            flag = false;
          } else {
            jQuery("input[name=" + item.name + "]")
              .next()
              .html("");
          }
        }
      }
      return flag;
    },
    success: function () {
      jQuery("#agile-cdn-loading").hide();
      jQuery("#agile-cdn-tip").fadeIn();
      setTimeout(() => {
        jQuery("#agile-cdn-tip").fadeOut();
      }, 3000);
      if (jQuery("#agliecdn-submit-botton-dot").hasClass("dot")) {
        jQuery("#agliecdn-submit-botton-dot").removeClass("dot");
      }
    }
  });
});
