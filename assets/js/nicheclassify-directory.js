document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('.nc-ajax-filter-form');
  const resultBox = document.getElementById('nc-ajax-listings');

  if (!resultBox) return;

  function loadListings(url) {
    resultBox.classList.add('loading');
    fetch(url)
      .then(res => res.text())
      .then(html => {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const newContent = tempDiv.querySelector('#nc-ajax-listings');
        if (newContent) resultBox.innerHTML = newContent.innerHTML;
      })
      .finally(() => {
        resultBox.classList.remove('loading');
      });
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(form);
      const params = new URLSearchParams(formData).toString();
      const url = window.location.pathname + '?' + params + '&ajax=1';
      loadListings(url);
    });
  }

  // Handle AJAX pagination
  document.addEventListener('click', function (e) {
    const link = e.target.closest('.nc-pagination a');
    if (link && resultBox.contains(link)) {
      e.preventDefault();
      const url = link.href + '&ajax=1';
      loadListings(url);
    }
  });

  const mapContainer = document.getElementById('nc-location-map');
  const latInput = document.querySelector('input[name="location_lat"]');
  const lngInput = document.querySelector('input[name="location_lng"]');
  const locationWrapper = mapContainer?.closest('.nc-field-type-location');
  if (locationWrapper) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'button nc-use-my-location';
    button.textContent = 'Use My Location';
    locationWrapper.insertBefore(button, mapContainer);
  }

  if (mapContainer && typeof L !== 'undefined') {
    const defaultLat = parseFloat(latInput?.value) || 23.8103;
    const defaultLng = parseFloat(lngInput?.value) || 90.4125;

    const map = L.map(mapContainer).setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
    }).addTo(map);

    let marker = null;
    if (!isNaN(defaultLat) && !isNaN(defaultLng)) {
      marker = L.marker([defaultLat, defaultLng]).addTo(map);
    }

    map.on('click', function (e) {
      const { lat, lng } = e.latlng;
      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = L.marker([lat, lng]).addTo(map);
      }
      latInput.value = lat.toFixed(6);
      lngInput.value = lng.toFixed(6);
    });

    document.querySelector('.nc-use-my-location')?.addEventListener('click', function () {
      if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
      }

      navigator.geolocation.getCurrentPosition(function (position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        map.setView([lat, lng], 13);

        if (marker) {
          marker.setLatLng([lat, lng]);
        } else {
          marker = L.marker([lat, lng]).addTo(map);
        }

        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);

        // Reverse geocode to fill address
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
          .then(res => res.json())
          .then(data => {
            const addressInput = document.querySelector('input[name="location_address"]');
            if (data && data.display_name && addressInput) {
              addressInput.value = data.display_name;
            }
          })
          .catch(() => {
            console.warn('Reverse geocoding failed.');
          });

        const addressInput = document.querySelector('input[name="location_address"]');
        let autocompleteResults = null;

        if (addressInput) {
          autocompleteResults = document.createElement('ul');
          autocompleteResults.className = 'nc-autocomplete-results';
          addressInput.parentNode.appendChild(autocompleteResults);

          addressInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length < 3) {
              autocompleteResults.innerHTML = '';
              return;
            }

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1`)
              .then(res => res.json())
              .then(data => {
                autocompleteResults.innerHTML = '';
                data.slice(0, 5).forEach(place => {
                  const item = document.createElement('li');
                  item.textContent = place.display_name;
                  item.dataset.lat = place.lat;
                  item.dataset.lon = place.lon;
                  item.className = 'nc-autocomplete-item';
                  autocompleteResults.appendChild(item);
                });
              });
          });

          autocompleteResults.addEventListener('click', function (e) {
            if (e.target.matches('.nc-autocomplete-item')) {
              const lat = parseFloat(e.target.dataset.lat);
              const lng = parseFloat(e.target.dataset.lon);
              addressInput.value = e.target.textContent;
              latInput.value = lat.toFixed(6);
              lngInput.value = lng.toFixed(6);

              if (marker) {
                marker.setLatLng([lat, lng]);
              } else {
                marker = L.marker([lat, lng]).addTo(map);
              }

              map.setView([lat, lng], 13);
              autocompleteResults.innerHTML = '';
            }
          });
        }
      }, function () {
        alert('Unable to retrieve your location.');
      });
    });
  }
});
