/**
 * Hellodev Inventory Manager - CRUD warehouses
 */
jQuery(document).ready(function($) {


	function checkIfCountryCodeExists(array, value) {
		for(var i = 0; i < array.length; i++) {
			if(array[i].countryCode == value) {
				if (i > -1) {
					return true;
				}
				break;
			}
		}
		return false;
	}

	function getCountryCodeIndex(array, value) {
		for(var i = 0; i < array.length; i++) {
			if(array[i].countryCode == value) {
				if (i > -1) {
					return i;
				}
				break;
			}
		}
		return -1;
	}

	if($("#IM_Warehouse_countries").length) {

		// in case we add a country
		$("#hellodev-inventory-manager-add-country").click(function() {
			var country = $("#hellodev-inventory-manager-countries option:selected").text();
			var countryCode = $("#hellodev-inventory-manager-countries").val();
			var countryListRaw = $("#IM_Warehouse_countries").val();
			var countryList = new Array();
			// check if it already exists
			if(countryListRaw.length > 0) {
				var countryList = JSON.parse(countryListRaw);
			}
			// create an Array
			var object = {"country": country, "countryCode": countryCode};
			if(checkIfCountryCodeExists(countryList, countryCode) == false)
			{
				// push it to the list
				countryList.push(object);

				// save the list in the element
				$("#IM_Warehouse_countries").val(
					JSON.stringify(countryList)
				);

				// update the countries
				parseCountries();
			}
		});

		$(".hellodev-inventory-manager-delete").on("click", function(){ deleteElement(this) });

		//in case we delete a country
		function deleteElement(element) {
			var countryListRaw = $("#IM_Warehouse_countries").val();
			var value = $(element).children("input").val();
			$(element).parent("span").remove();

			var countryList = new Array();
			// check if it already exists
			if(countryListRaw.length > 0) {
				var countryList = JSON.parse(countryListRaw);
			}

			var i = getCountryCodeIndex(countryList, value);
			if(i >= 0) {
				countryList.splice(i, 1);
			}

			// save the list in the element
			$("#IM_Warehouse_countries").val(
				JSON.stringify(countryList)
			);
		}

		function parseCountries() {
			var countryListRaw = $("#IM_Warehouse_countries").val();
			var countryList = JSON.parse(countryListRaw);

			$("#hellodev-inventory-manager-countries-added").html("");

			var topMost = $("#hellodev-inventory-manager-countries-added");

			for(var i = 0; i < countryList.length; i++) {
				var parent = $("<span></span>");
				parent.attr("style", "padding: 0px 5px 0px 5px");
				parent.text(countryList[i].country);

				var link = $('<a></a>');
				link.attr("class", "hellodev-inventory-manager-delete");
				link.attr("style", "padding-left: 5px");
				link.text("x");
				link.attr("href", "#hellodev-inventory-manager-countries");
				link.on("click", function(){ deleteElement(this) });

				var hiddenInput =  $('<input></input>');
				hiddenInput.attr("type", "hidden");
				hiddenInput.attr("value", countryList[i].countryCode);

				link.append(hiddenInput);
				parent.append(link);
				topMost.append(parent);
			}
		}

		parseCountries();

	}

	if($("#IM_Warehouse_outlets").length) {

	// in case we add a country
	$("#hellodev-inventory-manager-add-outlet").click(function() {
		var country = $("#hellodev-inventory-manager-outlets option:selected").text();
		var countryCode = $("#hellodev-inventory-manager-outlets").val();
		var countryListRaw = $("#IM_Warehouse_outlets").val();
		var countryList = new Array();
		// check if it already exists
		if(countryListRaw.length > 0) {
			var countryList = JSON.parse(countryListRaw);
		}
		// create an Array
		var object = {"country": country, "countryCode": countryCode};
		if(checkIfCountryCodeExists(countryList, countryCode) == false)
		{
			// push it to the list
			countryList.push(object);

			// save the list in the element
			$("#IM_Warehouse_outlets").val(
				JSON.stringify(countryList)
			);

			// update the countries
			parseOutlets();
		}
	});

	function parseOutlets() {
		var countryListRaw = $("#IM_Warehouse_outlets").val();
		var countryList = JSON.parse(countryListRaw);

		$("#hellodev-inventory-manager-outlets-added").html("");

		var topMost = $("#hellodev-inventory-manager-outlets-added");

		for(var i = 0; i < countryList.length; i++) {
			var parent = $("<span></span>");
			parent.attr("style", "padding: 0px 5px 0px 5px");
			parent.text(countryList[i].country);

			var link = $('<a></a>');
			link.attr("class", "hellodev-inventory-manager-delete-outlet");
			link.attr("style", "padding-left: 5px");
			link.text("x");
			link.attr("href", "#hellodev-inventory-manager-outlets");
			link.on("click", function(){ deleteElement2(this) });

			var hiddenInput =  $('<input></input>');
			hiddenInput.attr("type", "hidden");
			hiddenInput.attr("value", countryList[i].countryCode);

			link.append(hiddenInput);
			parent.append(link);
			topMost.append(parent);
		}
	}

	$(".hellodev-inventory-manager-delete-outlet").on("click", function(){ deleteElement2(this) });

	//in case we delete a country
	function deleteElement2(element) {
		var countryListRaw = $("#IM_Warehouse_outlets").val();
		var value = $(element).children("input").val();
		$(element).parent("span").remove();

		var countryList = new Array();
		// check if it already exists
		if(countryListRaw.length > 0) {
			var countryList = JSON.parse(countryListRaw);
		}

		var i = getCountryCodeIndex(countryList, value);
		if(i >= 0) {
			countryList.splice(i, 1);
		}

		// save the list in the element
		$("#IM_Warehouse_outlets").val(
			JSON.stringify(countryList)
		);
	}

	parseOutlets();

	}
});
