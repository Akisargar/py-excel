(function ($, Drupal) {
  let activeIndex;
  Drupal.behaviors.DistributorsMap = {
  attach: function (context, settings) {
    let cardElementsWrapper = $('.find-installers-results-wrapper');
    let cardElements = $('.find-installers-results-wrapper .installer-distributor-card, .view-id-showrooms_listing .installer-distributor-card');
    let map;
    
    // Highlight map marker on distributor listing item click.
    $(".view-id-millboard_find_installers .installer-distributor-card, .view-id-showrooms_listing .installer-distributor-card").click(function () {
      let cardIndex = $(this).attr('ind');
      showMeMarker(cardIndex)
    });

    // Sometime marrkers are not ready and need event refresh.
    let markersAreReady = setInterval(function() {
      let mapId = $('.geolocation-map-wrapper').attr('id');
      map = Drupal.geolocation.getMapById(mapId);
      let numberOfDistributorsCards  =  $('.installer-distributor-card').length;
      if(activeIndex > numberOfDistributorsCards) {
        activeIndex = 0;
      }

      if(numberOfDistributorsCards != map.mapMarkers.length) {
        map.removeMapMarkers(); // Remove all markers and redraw again
        let locations =  $('.installer-distributor-card');
        locations.each(function () {
        locationData = $(this).data('location');
        let title = $(this).attr('title')
          if(locationData){
            locationData = locationData.split(',')
            addMarker({lat:locationData[0],lng: locationData[1]}, map, title)
          }
        })
        map.fitMapToMarkers();
      }

      if(numberOfDistributorsCards == $('div[role="img"]').length) {
        clearInterval(markersAreReady);
        $(cardElements).each(function(index){
          $(this).attr('ind', index)
        })

        let mapMarkers = $('div[role="img"]');
        let isMobile = isMobileDevice();
        mapMarkers.on(isMobile ? 'touchend' : 'click',function () {
          clickedMakerIndex = $(this).index()-1;
          showMeMarker(clickedMakerIndex, map);
        })
        map.fitMapToMarkers();
      };

    }, 250);

    // Highlight map marker.
    let showMeMarker = function (index) {
      map.mapMarkers[activeIndex].setIcon('/themes/custom/millboard/svg/map_marker.svg');
      map.mapMarkers[activeIndex].setZIndex(99999);
      map.mapMarkers[index].setIcon('/themes/custom/millboard/svg/map_marker_active.svg');
      map.mapMarkers[index].setZIndex(999999999);
      activeIndex = index;
      cardElementsWrapper.scrollTo(cardElements[index], 300);
    }

    // Add Marker
    function addMarker(location, map, title) {
      let newMarker = new google.maps.Marker({
        position: new google.maps.LatLng(parseFloat(location.lat), parseFloat(location.lng)),
        icon: '/themes/custom/millboard/svg/map_marker.svg',
        title: title,
        setMarker: true
      });
      newMarker.setMap(map.googleMap);
      map.mapMarkers.push(newMarker);
    }

    // Initial map and click events.
    let onceMarkerIsReady = setInterval(function() {
      if ($('div[role="img"]').length) {
          let mapMarkers = $('div[role="img"]')
          let mapId = $('.geolocation-map-wrapper').attr('id');
          map = Drupal.geolocation.getMapById(mapId);

          if(activeIndex == undefined) {
            map.mapMarkers[0].setIcon('/themes/custom/millboard/svg/map_marker_active.svg');
            activeIndex = 0;
            map.mapMarkers[0].setZIndex(999999999);
          }
          clearInterval(onceMarkerIsReady);
          let isMobile = isMobileDevice();
          mapMarkers.on(isMobile ? 'touchend' : 'click',function () {
            clickedMakerIndex = $(this).index()-1;
            showMeMarker(clickedMakerIndex, map);
          })

          // Update/add markers on view more.
          if($('.installers-card-wrapper').length > 1) {
              let locations =  $('.installers-card-wrapper:last .installer-distributor-card');
              locations.each(function () {
              locationData = $(this).data('location');
              let title = $(this).attr('title')
              if(locationData){
                locationData = locationData.split(',')
              }
              addMarker({lat:locationData[0],lng: locationData[1]}, map, title)
            })
            map.fitMapToMarkers();
          }
      }
    }, 250);
  }}

  function isMobileDevice() {
    return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
  }
//   const maxLoadTries = 20;
//   let loadTries = 0;
//   function waitForMapLoad(id, cb) {
//     loadTries++;
//     if (loadTries > maxLoadTries) {
//         console.error('Map load timeout');
//         return;
//     }

//     let map = Drupal.geolocation.getMapById(id);
//     if (map === undefined || map.initialized === false) {
//       setTimeout(function () {
//         waitForMapLoad(id, cb);
//       }, 200);
//     } else {
//       cb();
//     }
//   }

//   function loadMap(id) {
//     let map = Drupal.geolocation.getMapById(id);
//     map.mapMarkers[0].setIcon('/themes/custom/millboard/svg/map_marker_active.svg');
//     $(".view-id-millboard_find_installers .views-field-nothing").click(function () {
//       let index = previous = $(this).index();
//       map.mapMarkers.forEach(function myFunction(item, ind) {
//         if(index === ind) {
//           item.setIcon('/themes/custom/millboard/svg/map_marker_active.svg');
//         } else {
//           item.setIcon('/themes/custom/millboard/svg/map_marker.svg');
//         }
//       });
//     });

//     // add a marker for the user's location - get from the url
//     /*let proximity = decodeURIComponent(window.location.search).match(/proximity=([^<]+)/);
//     if (proximity) {
//         let coords = proximity[1].split(',');
//         let latitude = coords[0];
//         let longitude = coords[1];
//         let userMarker = new google.maps.Marker({
//             position: new google.maps.LatLng(parseFloat(latitude), parseFloat(longitude)),
//             icon: '/themes/custom/millboard/svg/map_marker_home.svg',
//             title: 'Your location'
//         });

//         userMarker.setMap(map.googleMap);
//     }*/

//     setTimeout(
//         function () {
//           // Check if the user is on a mobile device
//           var isMobile = isMobileDevice();
//           $('div[role="img"]').on(isMobile ? 'touchend' : 'click',function () {
//             map.mapMarkers[0].setIcon('/themes/custom/millboard/svg/map_marker.svg');
//             map.mapMarkers[previous].setIcon('/themes/custom/millboard/svg/map_marker.svg');
//             let index = previous = $(this).index() -1;
//             map.mapMarkers[index].setIcon('/themes/custom/millboard/svg/map_marker_active.svg');
//             let cardElements = $('.find-installers-results-wrapper .views-field-nothing');
//             $('.find-installers-results-wrapper').scrollTo(cardElements[index], 300);
//           });
//         }, 1000);
//     function isMobileDevice() {
//       return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
//     }
//   }

  var geoOpt = {
    timeout: 5000,
    maximumAge: 100000,
    enableHighAccuracy: false
  }

  function doLocationUpdate(latitude, longitude) {
    // update the form fields for latitude and longitude.
    $('input#latitude').val(latitude);
    $('input#longitude').val(longitude);

    // submit the form.
    $('#millboard-installers-distributors-find-installer').find('input[type=submit][data-drupal-selector="edit-submit"]').click();
  }

  Drupal.behaviors.fetchUserLocation = {
    attach: function (context, settings) {
      $('#use-location').click(function (e) {
        e.preventDefault();
        $('.geoerror').html('');

        // if we can get user location from local storage, use it.
        let userLocation = JSON.parse(localStorage.getItem('userLocation')||'{}');

        if (userLocation && userLocation.latitude && userLocation.longitude) {
            console.log('Location fetched from local storage.', userLocation);
            doLocationUpdate(userLocation.latitude, userLocation.longitude);

            return;
        }

        if (navigator.geolocation) {
          console.log('Falling back to fetching location instead...');
          let opts = JSON.parse(JSON.stringify(geoOpt));
          opts.timeout = 10000;

          navigator.geolocation.getCurrentPosition(geoSucc, geoErr, opts);
        }
        else {
          console.log('Geolocation is not supported by this browser.');
            $('.geoerror').html(Drupal.t('Geolocation is not supported by this browser. Please enter a town or postcode manually.'));
        }

        function geoSucc(position) {
          console.log('Location fetched successfully.', position);
          var latitude = position.coords.latitude;
          var longitude = position.coords.longitude;

          doLocationUpdate(latitude, longitude);
        }

        function geoErr(error) {
          console.log('Error occurred while fetching location.', error);
          switch (error.code) {
            case error.PERMISSION_DENIED:
              $('.geoerror').html(Drupal.t('Please enable location services on your browser or device to use your location, or enter a town or postcode manually.'));
              break;

            case error.POSITION_UNAVAILABLE:
              $('.geoerror').html(Drupal.t('Location information is unavailable. Please enter a town or postcode manually.'));
              console.log('Location information is unavailable.');
              break;

            case error.TIMEOUT:
              $('.geoerror').html(Drupal.t('The request to get your location timed out. Please enter a town or postcode manually.'));
              console.log('The request to get user location timed out.');
              break;

            case error.UNKNOWN_ERROR:
              $('.geoerror').html(Drupal.t('An unknown error occurred getting your location. Please enter a town or postcode manually.'));
              console.log('An unknown error occurred.');
              break;
          }
        }

      });

      // try watchPosition and set into local storage for faster loading
      if (navigator.geolocation) {
        navigator.geolocation.watchPosition(function (position) {
          let obj = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          };

          // check it actually needs updating
          let userLocation = JSON.parse(localStorage.getItem('userLocation')||'{}');
          if (userLocation.latitude === obj.latitude && userLocation.longitude === obj.longitude) {
              return;
          }

          localStorage.setItem('userLocation', JSON.stringify(obj));
        }, function (error) {
            // console.error('Error occurred while fetching location.', error);
        }, geoOpt);
      }

      // Clear all button.
      $(".views-exposed-form input[data-drupal-selector=edit-reset]").click(function (e) {
        e.preventDefault();
        location.reload();
      })

      //Change google map marker icon.
      // if($('.geolocation-map-wrapper').length > 0) {
      //   let id = $('.geolocation-map-wrapper').attr('id');
      //   let previous = 0;
      //   var map = Drupal.geolocation.getMapById(id);
      //   if (map === undefined || map.initialized === false) {
      //     waitForMapLoad(id, () => {
      //       loadMap(id);
      //     });
      //   } else {
      //       loadMap(id);
      //   }
      // }
    }
  };
})(jQuery, Drupal);
