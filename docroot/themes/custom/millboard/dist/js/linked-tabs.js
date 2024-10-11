"use strict";

(function ($, Drupal, once) {
  'use strict';

  // calculate and return header + nav-wrapper height
  function headerHeight() {
    var headerHeight = $('.site-header').outerHeight() || 0;
    var navWrapperHeight = $('.nav-wrapper').outerHeight() || 0;
    return headerHeight + navWrapperHeight;
  }

  //Updating progress bar on click
  function updateProgressBar(currentTab, totalTabs) {
    var progressWidth;
    var progressWidthDesktop;
    if (currentTab === totalTabs) {
      // If the current tab is the last one, set the width to 100%
      progressWidth = "100%";
      progressWidthDesktop = "100%";
    } else {
      // Calculate the progress width based on the current tab and total tabs
      progressWidth = currentTab / totalTabs * 100 + "%";

      // Calculate the progress width for desktop view based on the clicked tab's width and offset
      var $clickedTab = $(".linked-tabs-nav li:eq(" + (currentTab - 1) + ") a");
      var tabWidth = $clickedTab.outerWidth();
      var tabOffset = $clickedTab.offset().left - $(".linked-tabs-nav").offset().left;
      progressWidthDesktop = tabWidth + tabOffset;
    }

    // Apply the width styles to the progress bar
    if (window.innerWidth <= 767) {
      $(".progress-bar").css({
        "width": progressWidth
      });
    } else {
      $(".progress-bar").css({
        "width": progressWidthDesktop,
        "max-width": "100%"
      });
    }
  }
  function updateActiveTabOnScroll() {
    var navWrapper = $('.nav-wrapper');
    if (navWrapper.length > 0) {
      var navWrapperBottom = $('.nav-wrapper').offset().top + $('.nav-wrapper').outerHeight();
      // Initialize a variable to keep track of the currently active index
      var activeIndex;

      // Loop through each tab to find which one is on the screen
      $(".linked-tabs-nav li").each(function (index) {
        var tabId = $(this).find('a').attr('href');
        var tabTop = $(tabId).offset().top;
        var tabBottom = tabTop + $(tabId).outerHeight();
        if (tabTop <= navWrapperBottom && tabBottom >= navWrapperBottom) {
          // Toggle the "active" class for the current tab
          $(".linked-tabs-nav li").removeClass("active").attr("aria-selected", "false");
          $(this).addClass("active").attr("aria-selected", "true");

          // Update the progress bar
          updateProgressBar(index + 1, $(".linked-tabs-nav li").length);
          if (window.innerWidth <= 767) {
            $('.linked-tabs-dropdown .selected-option').text($(this).find('a').text());
          }
          // Save the active index
          activeIndex = index;
        }
      });
      $(".linked-tabs-dropdown li").removeClass("active").attr("aria-selected", "false");
      $(".linked-tabs-dropdown li").eq(activeIndex).addClass("active").attr("aria-selected", "true");
    }
  }
  //Attaching behavior
  Drupal.behaviors.linkedTabs = {
    attach: function attach(context, setting) {
      function calculateTopValue() {
        var topValue = $('.site-header').outerHeight() || 0;
        var windowHeight = window.innerHeight;
        var documentHeight = $(document).height();
        var scrollTop = $(window).scrollTop();
        if (scrollTop + windowHeight >= documentHeight) {
          // Set the position to static at the bottom of the page
          $(".nav-wrapper").css({
            'position': 'static',
            'top': 'auto'
          });
          return;
        }

        // topValue += 1;
        $(".nav-wrapper").css({
          'position': 'sticky',
          'top': topValue + "px"
        });
      }

      //Calling function on page load and scroll
      calculateTopValue();
      $(window).on('scroll', function () {
        calculateTopValue();
        updateActiveTabOnScroll();
      });
      var linkedTabComponents = once("linked_tabs_nav_create", ".linked_tabs.coh-component", context);
      if (linkedTabComponents.length > 0) {
        var scrollToTab = function scrollToTab(targetTabContentId, offsetValue) {
          // Animate the scroll to the target tab content with the offset
          $('html, body').animate({
            scrollTop: $(targetTabContentId).offset().top - offsetValue
          }, 1500);
        }; // Smooth scroll to tab content on tab item click
        var linkedNavWrapper = "<div class='nav-wrapper' role='navigation'>";
        var linkedNav = "<nav class='linked-tabs-nav' role='tablist'><ul>";
        var idCounter = 1;
        //Creating nav
        linkedTabComponents.forEach(function (singleComponent, index) {
          if ($(singleComponent).find('h2.coh-heading.coh-component').length > 0) {
            var isActiveClass = index === 0 ? ' class="active" aria-selected="true"' : '';
            linkedNav += "<li role='tab' aria-controls='linked-tab-" + idCounter + "'" + isActiveClass + "><a href='#linked-tab-" + idCounter + "'>" + $(singleComponent).find('h2.coh-heading.coh-component').first().text() + "</a></li>";
            $(singleComponent).attr("id", "linked-tab-" + idCounter);
            idCounter++;
          }
        });
        linkedNav += "</ul></nav>";
        linkedNavWrapper += linkedNav + "</div>";
        $(linkedTabComponents[0]).before(linkedNavWrapper);
        $(".linked-tabs-nav li:first-child").addClass("active");
        $(".linked-tabs-nav").after("<div class='progress'><span id='linked-tabs-nav' class='progress-bar'></span></div>");
        updateProgressBar(1, linkedTabComponents.length);
        $(".linked-tabs-nav a").on("click", function () {
          var currentTab = $(".linked-tabs-nav a").index($(this)) + 1;
          updateProgressBar(currentTab, linkedTabComponents.length);
          $(this).parent().addClass("active").attr("aria-selected", "true");
          $(".linked-tabs-nav li").not($(this).parent()).removeClass("active").attr("aria-selected", "false");
          $(".linked-tabs-dropdown .selected-option").text($(".linked-tabs-nav li.active a").text());
        });

        //Creating dropdown for mobile
        var dropdownHTML = '<div class="linked-tabs-dropdown" role="select" aria-expanded="false">';
        dropdownHTML += '<button class="selected-option">' + $(".linked-tabs-nav li:first-child a").text() + '</button>';
        dropdownHTML += '<ul class="dropdown-list">';
        //Creating dropdown list
        linkedTabComponents.forEach(function (singleComponent, index) {
          if ($(singleComponent).find('h2.coh-heading.coh-component').length > 0) {
            var tabId = "#linked-tab-" + (index + 1);
            var tabText = $(singleComponent).find('h2.coh-heading.coh-component').first().text();
            var isActiveClass = index === 0 ? ' class="active"' : '';
            dropdownHTML += '<li role="tab" data-tab="' + tabId + '"' + isActiveClass + ' aria-controls="linked-tab-' + (index + 1) + '">' + tabText + '</li>';
          }
        });
        dropdownHTML += '</ul></div>';
        $(".linked-tabs-nav").after(dropdownHTML);
        $('.linked-tabs-dropdown').on('click', '.selected-option', function (e) {
          $(this).toggleClass("expanded");
          $(".linked-tabs-dropdown .dropdown-list").slideToggle(300, function () {
            if ($(".linked-tabs-dropdown .selected-option").hasClass("expanded")) {
              $(".linked-tabs-dropdown").attr("aria-expanded", "true");
              $(".linked-tabs-dropdown .dropdown-list li.active").focus();
            } else {
              $(".linked-tabs-dropdown").attr("aria-expanded", "false");
            }
          });
        });
        //Dropdown keyboard accessibility
        $('.linked-tabs-dropdown').on('keydown', '.selected-option', function (e) {
          if (e.which === 13 || e.which === 32) {
            e.preventDefault();
          }
          if (e.type === 'click' || e.type === 'keydown' && (e.which === 13 || e.which === 32)) {
            $(this).toggleClass("expanded");
            $(".linked-tabs-dropdown .dropdown-list").slideToggle(300, function () {
              if ($(".linked-tabs-dropdown .selected-option").hasClass("expanded")) {
                $(".linked-tabs-dropdown").attr("aria-expanded", "true");
                $(".linked-tabs-dropdown .dropdown-list li.active").focus();
              } else {
                $(".linked-tabs-dropdown").attr("aria-expanded", "false");
              }
            });
          }
        });
        //Dropdown list keyboard accessibility
        $('.linked-tabs-dropdown').on('click', '.dropdown-list li', function (e) {
          var selectedTab = $(this).data('tab');
          var currentTab = parseInt(selectedTab.split('-')[2]);
          var offsetValue = headerHeight();
          $('.linked-tabs-dropdown .dropdown-list li').removeClass('active');
          $(this).addClass('active');
          updateProgressBar(currentTab, linkedTabComponents.length);
          $(".linked-tabs-nav li").removeClass("active").attr("aria-selected", "false");
          $(".linked-tabs-nav a[href='" + selectedTab + "']").parent().addClass("active").attr("aria-selected", "true");
          $('html, body').animate({
            scrollTop: $(selectedTab).offset().top - offsetValue
          }, {
            duration: 1500,
            complete: function complete() {
              $(".linked-tabs-dropdown .selected-option").removeClass("expanded").attr("aria-expanded", "false");
            }
          });
          $(".linked-tabs-dropdown .selected-option").text($(".linked-tabs-nav li.active").text());
          $(".linked-tabs-dropdown .dropdown-list").slideUp(300);
        });
        //Keyboard accessibility for nav
        $(".linked-tabs-nav a").on("keydown", function (e) {
          if (e.which === 13 || e.which === 32) {
            e.preventDefault();
            $(this).click();
          } else if (e.which === 37 || e.which === 39) {
            e.preventDefault();
            var currentIndex = $(".linked-tabs-nav li").index($(this).parent());
            var nextIndex;
            if (e.which === 37) {
              nextIndex = Math.max(0, currentIndex - 1);
            } else {
              nextIndex = Math.min(currentIndex + 1, $(".linked-tabs-nav li").length - 1);
            }
            $(".linked-tabs-nav li").eq(nextIndex).find('a').focus().click();
          }
        });
        //Keyboard accessibility for dropdown list.
        $('.linked-tabs-dropdown').on('keydown', '.dropdown-list li:last-child', function (e) {
          if (e.which === 9 && !e.shiftKey) {
            e.preventDefault();
            $(".linked-tabs-dropdown .selected-option").focus();
          }
        });

        // Function to scroll to a specific tab
        var currentTabIndex = 0;
        $('.linked-tabs-nav a').on('click', function (event) {
          event.preventDefault();

          // Get the target tab content ID from the href attribute
          var targetTabContentId = $(this).attr('href');

          // Determine whether the clicked tab is a previous or next tab
          var clickedTabIndex = $('.linked-tabs-nav a').index(this);
          if (clickedTabIndex < currentTabIndex) {
            // Scroll to the prev tab
            scrollToTab(targetTabContentId, headerHeight());
          } else if (clickedTabIndex > currentTabIndex) {
            // Scroll to the next tab
            scrollToTab(targetTabContentId, headerHeight());
          } else {
            // Clicked on the Current Tab
          }

          // Update current tab index
          currentTabIndex = clickedTabIndex;
        });
      }
    }
  };
})(jQuery, Drupal, once);