function findCountryId(countryName) {
  return window.countries.findIndex(country => country.country == countryName);
}

function findCountryStateId(countryName, stateName) {
  const countryId = findCountryId(countryName);
  if (countryId && window.countries[countryId].states) {
    const states = window.countries[countryId].states;
    return states.findIndex(state => state == stateName);
  }
  return -1;
}

function initCountryInput(inputId) {
  var countrySelectHtml = `
    <h5>الدولة:</h5>
    <select class="form-control country-input-name-select" name="country">
      <option value="">الدولة</option>`;
  for(let i = 0; i < window.countries.length; i++) {
    countrySelectHtml += `<option value="${window.countries[i].country}">
      ${window.countries[i].country}
    </option>`;
  }
  countrySelectHtml += `  </select>`;
  $(`#${inputId} .country-input-name`).html(countrySelectHtml);
}

function updateCountryStataes(inputId, countryName) {
  $(`#${inputId} .country-input-state`).html('');
  var countryStatesHtml = `
    <h5>الولاية:</h5>
    <select class="form-control country-input-state-select" name="state">
      <option value="">الولاية</option>`;
  const countryId = findCountryId(countryName);
  if (countryId != -1 && window.countries[countryId].states) {
    var states = window.countries[findCountryId(countryName)].states
    for(let i = 0; i < states.length; i++) {
      countryStatesHtml += `<option value="${states[i]}">
        ${states[i]}
      </option>`;
    }
    countryStatesHtml += `  </select>`;
    $(`#${inputId} .country-input-state`).html(countryStatesHtml);
  }
}

function initCountryInputs() {
  var countryInputs = $('.country-input');
  for(let i = 0; i < countryInputs.length; i++) {
    initCountryInput($(countryInputs[i]).attr('id'));
  }
}

$(document).ready(function() {
  $.getJSON('resources/js/address-input/countries.json', function(data) {
    window.window.countries = data;
    initCountryInputs();
  });
  $on('.country-input-name select', 'change', function() {
    updateCountryStataes(getElementparent(this, 2).id, $(this).val());
  });

});
