function printpage() { window.print(); }

var recordSwapTarget;
var colormarking;
var trendselection;
var trendcrosshair;
var trendcolors = Array();

function daysInMonth(month, year) {
	return new Date(year, month, 0).getDate();
}

function updateNotifications( text, status, notif, records) {
	if (typeof(records) === 'undefined') {
		records = false;
	};
	notif.remove();
	if (status != "complete") {
		$("#notifications").append('<div id="ajax-notification" class="ui-notif-error"><span class="icon-remove-sign"></span>' + text + '</div>');
		if (records) {
			records.not(".ajax").css("display", "table");
		};
		fRemoveParent();
	};
};

function fRemoveParent() {
	$('#notifications .icon-remove-sign').click(function () {	
		$(this).parent().remove();
	});
};

function toUpperCaseFirst(str) {
	return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

function checkLength( o, n, min, max ) {
if ( o.val().length > max || o.val().length < min ) {
	o.addClass( "ui-state-error" );
	updateTips( "Length of " + n + " must be between " + min + " and " + max + "." );
	return false;
  } else {
	return true;
  }
};

/*function fCheckboxTrends(jThis) {
	console.log(jThis);
	if (shiftDown) {
		$(jThis).parent().parent().parent().find('input:checkbox').each(function() {
			this.checked = false;
		});
		$(jThis).prop("checked", "true");
	};
};*/

function updateTips( t ) {
	var validatetip = $('.validateTips');
	if (validatetip.length <= 0) {
		$("#notifications").append('<div class="ui-notif-error validateTips"><span class="icon-remove-sign"></span>' + t + '</div>');
	} else {
		validatetip.html( '<span class="icon-remove-sign"></span>' + t ).addClass( "ui-notif-error" );
	};
	fRemoveParent();
};

function showDisc(sorterTable) {
	// Toggle child row content (td), not hiding the row since we are using rowspan
	// Using delegate because the pager plugin rebuilds the table after each page change
	// "delegate" works in jQuery 1.4.2+; use "live" back to v1.3; for older jQuery - SOL
	var toggleDisc = $(sorterTable + " .toggle-table-sorter").on("click" ,function(){
		// use "nextUntil" to toggle multiple child rows
		// toggle table cells instead of the row
		var jThis = $(this);
		if (jThis.html() == "Show") {
			jThis.html("Hide");
		} else {
			jThis.html("Show");
		}
		//jThis.parent().parent().next("tr.tablesorter-hasChildRow").find("td").toggle();
		jThis.parent().parent().next("tr").find("td").toggle();
		return false;
	});
	$(sorterTable + " #toggle-alarm-comments").on("click", function() {
		$(toggleDisc).trigger("click");
		return false;
	});
};

function checkNumber(o, n) {
	if (parseFloat(o.val()) != o.val()) {
		o.addClass( "ui-state-error" );
		updateTips( n );
		return false;
	} else {
		return true;
	};
}

function checkRegexp( o, regexp, n ) {
	//console.log(o.val());
  if ( !( regexp.test( o.val() ) ) ) {
	o.addClass( "ui-state-error" );
	updateTips( n );
	return false;
  } else {
	return true;
  }
};

function checkTextBox (o, n) {
	if (o.find("iframe").contents().find("body").text() == "") {
		updateTips("The Comment must contain text.");
		$("#bugbody").addClass( "ui-state-error" );
		return false;
	} else {
		return true;
	};
};

function checkMatch(o, p, n) {
	if (o.val() != p.val()) {
		o.addClass( "ui-state-error" );
		p.addClass( "ui-state-error" );
		updateTips( n );
		return false;
	} else {
		return true;
	};
};

function checkOptionSelected(o, n) {
	if (o.val() == "none" || o.val() == null) {
		o.addClass( "ui-state-error" );
		updateTips( n );
		return false;
	} else {
		return true;
	};
};
	
function showTooltip(x, y, contents) {
	var offset = 15;
	var windowWidthMid = $(window).width() / 2;
	var windowWidth = $(window).width();
	var windowHeightMid = $(window).height() / 2;
	var windowHeight = $(window).height();
	var windowCursorY = y - $(window).scrollTop();
	var windowCursorX = x - $(window).scrollLeft();
	var tooltip = $('<div id="tooltip">' + contents + '</div>');
	/*$(tooltip).css( {
		position: 'absolute',
		top: y + 15,
		left: x + 15
	}).appendTo("body");//.fadeIn(200);*/
	tooltip.css('position', 'fixed');
	if (windowCursorX <= windowWidthMid) {
		tooltip.css('left', windowCursorX + offset);
	} else {
		tooltip.css('right', (windowWidth - windowCursorX) + offset);
	};
	if (windowCursorY <= windowHeightMid) {
		tooltip.css('top', windowCursorY + offset);
	} else {
		tooltip.css('bottom', (windowHeight - windowCursorY) + offset);
	};
	tooltip.appendTo("body");
};
function showTooltipEle(x, y, contents) {
	var offset = 15;
	var windowWidthMid = $(window).width() / 2;
	var windowWidth = $(window).width();
	var windowHeightMid = $(window).height() / 2;
	var windowHeight = $(window).height();
	var windowCursorY = y;
	var windowCursorX = x;
	var tooltip = $('<div id="tooltip">' + contents + '</div>');
	/*$(tooltip).css( {
		position: 'absolute',
		top: y + 15,
		left: x + 15
	}).appendTo("body");//.fadeIn(200);*/
	tooltip.css('position', 'fixed');
	if (windowCursorX <= windowWidthMid) {
		tooltip.css('left', windowCursorX + offset);
	} else {
		tooltip.css('right', (windowWidth - windowCursorX) + offset);
	};
	if (windowCursorY <= windowHeightMid) {
		tooltip.css('top', windowCursorY + offset);
	} else {
		tooltip.css('bottom', (windowHeight - windowCursorY) + offset);
	};
	tooltip.appendTo("body");
};
function msToTime(time) {
	seconds = time / 1000;
	var duration = new Array();
	
	
	var neg = '';
	if (seconds < 0) {
		neg = '-';
		seconds = seconds * -1;
	};
	days = Math.floor(seconds / 60 / 60 / 24);
	seconds = seconds - (24 * 60 * 60 * days);
	if (days >= 1) {
		duration.push(days + 'd');
	};
	
	hours = Math.floor(seconds / 60 / 60);
	seconds = seconds - (hours * 60 * 60);
	if (hours >= 1) {
		duration.push(hours + 'h');
	};
	minutes = Math.floor(seconds / 60);
	seconds = seconds - (minutes * 60);
	if (minutes >= 1) {
		duration.push(minutes + 'm');
	};
	if (seconds >= 1) {
		duration.push(Math.round(seconds) + 's');
	};
	return neg + duration.join(' ');
};
var durTicks = function (axis) {
	//console.log(axis);
	/*axis.datamax
	axis.datamin
	axis.delta
	axis.max
	axis.min*/
	var tickCount = 6, ticker, ticks = new Array(), minAdj;
	axis.tickSize = (axis.max - axis.min) / tickCount;
	if (axis.tickSize >= 86400) {
		axis.minTickSize = 86400;
	} else if (axis.tickSize >= 3600) {
		axis.minTickSize = 3600;
	} else if (axis.tickSize >= 60) {
		axis.minTickSize = 60;
	} else if (axis.tickSize >= 1) {
		axis.minTickSize = 1;
	};
	minAdj = Math.floor(axis.min / axis.minTickSize) * axis.minTickSize;
	for (i = 1; i < tickCount; i++) {
		ticker = (Math.floor((axis.tickSize * i) / axis.minTickSize) * axis.minTickSize) + minAdj;
		ticks.push([ticker, msToTime(ticker * 1000)]);
	}
	return ticks;
};

	function fDatePicker (datepicker, field, minDate, mode) {
		if (mode == 'datetime') {
			bDateTime = true;
		} else {
			bDateTime = false;
		};
		minYear = minDate[0];
		minMonth = minDate[1] - 1;
		minDay = minDate[2];
		var date = new Date(minYear, minMonth, minDay);
		var endDate = new Date();
		if (bDateTime == true) {
			$(datepicker).datetimepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				changeMonth: true,
				changeYear: true,
				altField: field,
				altFormat: 'yy-mm-dd',
				minDate: date,
				maxDate: endDate,
				showWeek: 1,
				firstDay: 1,
				showButtonPanel: false,
				altTimeFormat: 'HH:mm:ss',
				altFieldTimeOnly: false,
				hour: endDate.getHours(),
				minute: endDate.getMinutes(),
				second: endDate.getSeconds(),
				pickerTimeFormat: "hh:mm:ss TT",
				addSliderAccess: true,
				sliderAccessArgs: { touchonly: false }
			});
		} else {
			$(datepicker).datepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				changeMonth: true,
				changeYear: true,
				altField: field,
				altFormat: 'yy-mm-dd',
				minDate: date,
				maxDate: endDate,
				showWeek: 1,
				firstDay: 1
			});
		};
	};

fMarkings = function(axes) {
	var markings = [];
	var date = new Date(axes.xaxis.min);
	if (Math.floor(date/(24 * 60 * 60 * 1000)) % 2 == 1) {
		date = new Date(date - (24 * 60 * 60 * 1000));
	};
	date.setSeconds(0);
	date.setMinutes(0);
	date.setHours(0);
	var i = date.getTime();
	do {
		markings.push({xaxis:{from: i, to: i + (24 * 60 * 60 * 1000) }, color: colormarking } );
		i += ((24 * 60 * 60 * 1000) * 2);
	} while (i < axes.xaxis.max);
	return markings;
};

function fTableSorter(options) {
		/*'sorttable' => '#jQuery', 
		'sortorder' => '0', 
		'pagerid' => '#jQuery', 
		'headers' => '4 : { sorter: "duration" },
				6 : { sorter: "duration" },
				7 : { sorter: false }'*/
		var rowCount = $(options.sorttable + ' tbody tr').not('.hiddenrow').length;
		var pagerSize;
		if (rowCount >= 20) {
			pagerSize = 20;
		} else {
			pagerSize = rowCount;
		};
		$(options.sorttable).tablesorter({
			cssChildRow: "hiddenrow",
			widthFixed:true,
			sortList: options.sortorder, //[]
			headers: options.headers, //{}
			//textAttribute: "data-duration",
			widgets: ["zebra", "filter", "columnSelector", "pager", "output"], //math
			widgetOptions : {
				output_delivery: "download",
				output_saveRows: $("tr").not(".hiddenrow"),
				math_data: "math",
				math_complete: function($cell, wo, result, value, arry) {
					prefix = '';
					switch ($($cell).data("math")) {
						case 'col-count':
							prefix = 'Count: ';
							break;
						case 'col-count-child':
							prefix = 'Count: ';
							break;
						case 'col-sum':
							prefix = 'Total: ';
							break;
						case 'col-mean':
							prefix = 'Mean: ';
							value = Math.round(value);
							break;
						case 'col-time-sum':
							prefix = 'Total: ';
							value = 0;
							$cell.data("column");
							$cells = $cell.closest("table").find("tbody").find("[data-column=\'" + $cell.data("column") + "\']");
							$.each($cells, function() {
								value += $(this).data("duration");
							});
							value = msToTime(value * 1000)
							break;
						case 'col-time-mean':
							prefix = 'Mean: ';
							value = 0;
							$cell.data("column");
							$cells = $cell.closest("table").find("tbody").find("[data-column=\'" + $cell.data("column") + "\']");
							$.each($cells, function() {
								value += $(this).data("duration");
							});
							value /= $cells.length;
							value = msToTime(value * 1000);
							break;
						default:
							break;
					};
					return prefix + value;
				},
				filter_columnFilters:false,
				zebra : [ "oddRow", "evenRow" ],
				columnSelector_container: $('#' + options.rowheaders + '-table-rows'),
				columnSelector_mediaquery: false,
				columnSelector_saveColumns: false,
				columnSelector_layout: '<div class="columnselectoritem"><input type="checkbox" id="label-sort-{name}"><label for="label-sort-{name}">{name}</label></div>',
				pager_selectors: {
					container: $(options.sorttable + "-pager")
				},
				pager_size:pagerSize,
				pager_savePages:false,
				pager_removeRows:false,
				//pager_fixedHeight: true,
				//pager_storageKey:"alarms-sort-table",
				pager_output: "{page:input}/{filteredPages} (" + pagerSize + ")"			
			}
		});
		showDisc(options.sorttable);
	};
	function fRowButton(tThis) {
		var jThis = $(tThis);
		//console.log(jThis);
		var id = '#' + jThis.data('id') + '-table-rows';
		$('.tablesorter-row-hider').not(id).css('display', 'none');
		var offset = jThis.parent().offset();
		$(id).css("top", Math.round(parseInt(jThis.parent().css('padding-top')) + offset.top + jThis.height() + 5));
		$(id).css("left", Math.round(offset.left));
		$(id).toggle();
		return false;
	};
	
	
$.extend({
	ucwords : function(str) {
		strVal = '';
		str = str.split(' ');
		for (var chr = 0; chr < str.length; chr++) {
			strVal += str[chr].substring(0, 1).toUpperCase() + str[chr].substring(1, str[chr].length) + ' ';
		};
		return strVal;
	}
});
	
$(function() {	
	
	var css2js = $('<div id="markings"></div>').appendTo("body");
	colormarking = css2js.css("background-color");
	css2js.remove();
	css2js = $('<div id="trendcolor1"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendcolor2"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendcolor3"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendcolor4"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendcolor5"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendcolor6"></div>').appendTo("body");
	trendcolors.push(css2js.css("background-color"));
	css2js.remove();
	css2js = $('<div id="trendselection"></div>').appendTo("body");
	trendselection = css2js.css("background-color");
	css2js.remove();
	css2js = $('<div id="trendcrosshair"></div>').appendTo("body");
	trendcrosshair = css2js.css("background-color");
	css2js.remove();
	
	
	
	var previousPoint = null;
	
	fRemoveParent();
	
	delete $.tablesorter.filter.types.operators;
	delete $.tablesorter.filter.types.notMatch;
	delete $.tablesorter.filter.types.exact;
	delete $.tablesorter.filter.types.and;
	delete $.tablesorter.filter.types.range;
	delete $.tablesorter.filter.types.wild;
	delete $.tablesorter.filter.types.fuzzy;
	
	
	
	$.tablesorter.addParser({
		// set a unique id
		id: 'duration',
		is: function(s) {
			// return false so this parser is not auto detected
			return false;
		},
		format: function(s, table, cell, cellIndex) {
			var $cell = $(cell);
			// I could have used $(cell).data(), then we get back an object which contains both
			// data-lastname & data-date; but I wanted to make this demo a bit more straight-forward
			// and easier to understand.

			// first column (zero-based index) has lastname data attribute
			// returns lastname data-attribute, or cell text (s) if it doesn\'t exist
			return $cell.attr('data-duration') || s;
		},
		// flag for filter widget (true = ALWAYS search parsed values; false = search cell text)
		parsed: false,
		// set type, either numeric or text
		type: 'numeric'
	});
	$.tablesorter.addParser({
		// set a unique id
		id: 'checkbox',
		is: function(s) {
			// return false so this parser is not auto detected
			return false;
		},
		format: function(s, table, cell, cellIndex) {
			var $cell = $(cell);
			// I could have used $(cell).data(), then we get back an object which contains both
			// data-lastname & data-date; but I wanted to make this demo a bit more straight-forward
			// and easier to understand.

			// first column (zero-based index) has lastname data attribute
			// returns lastname data-attribute, or cell text (s) if it doesn\'t exist
			return $cell.attr('data-checkbox') || s;
		},
		// flag for filter widget (true = ALWAYS search parsed values; false = search cell text)
		parsed: false,
		// set type, either numeric or text
		type: 'text'
	});
	
	$.tablesorter.equations['count-child'] = function(array, config) {
		return  $.tablesorter.equations.count( array ) / 2;
	};
	$.tablesorter.equations['time-mean'] = function(array, config) {
		return null;
	};
	$.tablesorter.equations['time-sum'] = function(array, config) {
		return null;
	};
	
	function fAjaxSorter(id) {
		switch(id) {
			case 'ajax-alarm':
				if ($('#' + id + '-table-rows').length <= 0) {
					$('.content').append('<div id="' + id + '-table-rows" class="tablesorter-row-hider"></div>');
				} else {
					$('#' + id + '-table-rows').html('')
				};
				fTableSorter({sorttable: "#" + id,
					rowheaders: id,
					sortorder: [[1,0]], 
					headers: {4 : { sorter: "duration" },
							6 : { sorter: "duration", columnSelector: false },
					7 : { sorter: false }}
				});
			break;
			case 'ajax-alarm-single':
				if ($('#' + id + '-table-rows').length <= 0) {
					$('.content').append('<div id="' + id + '-table-rows" class="tablesorter-row-hider"></div>');
				} else {
					$('#' + id + '-table-rows').html('')
				};
				$('#' + id + '-row-button').click(function() {
					fRowButton(this);
					return false;
				});
				fTableSorter({sorttable: "#" + id, 
					rowheaders: id,
					sortorder: [[3,0]], 
					headers: {1 : { columnSelector: false },
							4 : { columnSelector: false },
							5 : { sorter: "duration", columnSelector: false },
							7 : { sorter: "duration" }}
				});
			break;
			case 'ajax-batch':
				if ($('#' + id + '-table-rows').length <= 0) {
					$('.content').append('<div id="' + id + '-table-rows" class="tablesorter-row-hider"><div>');
				} else {
					$('#' + id + '-table-rows').html('')
				};
				fTableSorter({sorttable: "#" + id, 
					rowheaders: id,
					sortorder: [[2,0]], 
					headers: {1 : { sorter: "duration" },
							4 : { columnSelector: false }}
				});
			break;
			case 'ajax-batch-phase':
				if ($('#' + id + '-table-rows').length <= 0) {
					$('.content').append('<div id="' + id + '-table-rows" class="tablesorter-row-hider"><div>');
				} else {
					$('#' + id + '-table-rows').html('')
				};
				$('#' + id + '-row-button').click(function() {
					fRowButton(this);
					//console.log('worked');
					return false;
				});
				fTableSorter({sorttable: "#" + id, 
					rowheaders: id,
					sortorder: [[2,0]], 
					headers: {1 : { sorter: "duration" },
							6 : { columnSelector: false }}
				});
			break;
			default:
			break;
		};
	};
	  
	var name = $( "#user" ),
    password = $( "#pass" ),
	currentPassword = $( "#currentpass" ),
	newPassword = $( "#newpass" ),
	confirmNewPassword = $( "#confirmnewpass" ),
    allLoginFields = $( [] ).add( name ).add( password ),
	allPasswordFields = $( [] ).add( currentPassword ).add( newPassword ).add( confirmNewPassword );
	$('#login').click(function() { 
		var bValid = true;
		allLoginFields.removeClass( "ui-state-error" );
		bValid = bValid && checkLength( name, "username", 3, 16 );
		bValid = bValid && checkLength( password, "password", 5, 16 );
		bValid = bValid && checkRegexp( name, /^[0-9a-zA-Z ]+$/, "Username must be Alphanumeric." );
		bValid = bValid && checkRegexp( password, /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
		if (bValid == false) {
			return false;
		}
	} );
	$('#password').click(function() { 
		var bValid = true;
		allPasswordFields.removeClass( "ui-state-error" );
		bValid = bValid && checkLength( currentPassword, "password", 5, 16 );
		bValid = bValid && checkLength( newPassword, "password", 5, 16 );
		bValid = bValid && checkLength( confirmNewPassword, "password", 5, 16 );
		bValid = bValid && checkRegexp( currentPassword, /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
		bValid = bValid && checkRegexp( newPassword, /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
		bValid = bValid && checkRegexp( confirmNewPassword, /^[0-9a-zA-Z\@\#\$\%\^\&\*\(\)\_\+\!]+$/, "Password may contain: 0-9 a-z @#$%^&*()_+" );
		bValid = bValid && checkMatch( confirmNewPassword, newPassword, "Fields do not match." );
		if (bValid == false) {
			return false;
			
		}
	} );
	function fChartSelect(jData) {
		if (jData.start != 0 && jData.end != 0) {
			if (recordSwapTarget.getOptions().selection.mode != null) {
				recordSwapTarget.setSelection({ 
					xaxis: { 
						from: jData.start, 
						to: jData.end 
					}
				});
			} else {
				options.xaxes[0].min = jData.start;
				options.xaxes[0].max = jData.end;
				redrawPlots();
				getAjaxData(jData.start, jData.end);
			};
		} else {
			updateNotifications("Action failed, Not a complete record", "failed", ajaxnotification);
		};
	};
	var oGet, recordClass, recordName;
	function fRecordDetail(jThis) {
		records = $(".records");
		records.css("display", "none");
		data = $(jThis).data();
		switch (data.url) {
			case "includes/ajax.alarm.single.php":
				oGet = { alarm: data.name, startdate: iDateStart, enddate: iDateEnd, department: data.department, equipment: data.equipment };
				recordClass = "alarm-single";
				var newSwapNodeHeaders = $('<a data-text="Table Headers" href="none.php" id="ajax-' + recordClass + '-row-button" data-id="ajax-' + recordClass + '"><span class="icon-list icon-hover-hint"></span></a>');
				var newSwapNodeRefresh = $('<a data-text="Refresh" data-id="ajax-' + recordClass + '" data-url="' + data.url + '" data-name="' + data.name + '" class="ajax-records" data-department="' + data.department + '" href="#"><span class="icon-refresh icon-hover-hint"></span></a>').click(function() {
					fRecordDetail(this);
					return false;
				});
				break;
			case "includes/ajax.batch.phase.php":
				oGet = { dbname: data.dbname, batch: data.batch, startdate: iDateStart, enddate: iDateEnd, department: data.department, equipment: data.equipment };
				recordClass = "batch-phase";
				var newSwapNodeHeaders = $('<a data-text="Table Headers" href="none.php" id="ajax-' + recordClass + '-row-button" data-id="ajax-' + recordClass + '"><span class="icon-list icon-hover-hint"></span></a>');
				var newSwapNodeRefresh = $('<a data-text="Refresh" data-id="ajax-' + recordClass + '" data-url="' + data.url + '" data-name="' + data.name + '" data-batch="' + data.batch + '"  data-dbname="' + data.dbname + '" class="ajax-records" data-department="' + data.department + '" href="#"><span class="icon-refresh icon-hover-hint"></span></a>').click(function() {
					fRecordDetail(this);
					return false;
				});
				break;
		};
		$.ajax({
			// the URL for the request
			url: data.url,
			// the data to send (will be converted to a query string)
			data: oGet,
			// whether this is a POST or GET request
			type: "GET",
			// the type of data we expect back
			dataType : "json",
			beforeSend: function() {
					$("#" + data.id).remove();
					updateNotifications("something", "complete", ajaxnotification, records);
					recordsSwap.after(loading);
				},
			// code to run if the request succeeds;
			// the response is passed to the function
			success: function( json ) {
					$('.' + recordClass + '-ul').remove();
					var newRecordUl = $('<li class="' + recordClass + '-ul"></li>');
					recordsSwap.find('ul').append(newRecordUl);
					var newSwapNode = $('<a data-id="ajax-' + recordClass + '" data-url="' + data.url + '" class="ajax-records" data-department="' + data.department + '" href="#">' + data.name + '</a>').click(function() {
						var records = $(".records");
						records.css("display", "none");
						$('#' + $(this).data('id')).css('display', 'table');
						return false;
					});
					newRecordUl.append(newSwapNode);
					newRecordUl.append(' ');
					newRecordUl.append(newSwapNodeHeaders);
						newSwapNodeHeaders.bind({
							mouseenter: function(e) {		
								$("#tooltip").remove();
								var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
								var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
								var midRel = [position[0] + mid[0], position[1] + mid[1]];
								showTooltipEle(midRel[0], midRel[1], $(this).data('text'));
							},
							mouseleave: function() {
								$("#tooltip").remove();
							}
						});
					newRecordUl.append(' ');
					newRecordUl.append(newSwapNodeRefresh);
						newSwapNodeRefresh.bind({
							mouseenter: function(e) {		
								$("#tooltip").remove();
								var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
								var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
								var midRel = [position[0] + mid[0], position[1] + mid[1]];
								showTooltipEle(midRel[0], midRel[1], $(this).data('text'));
							},
							mouseleave: function() {
								$("#tooltip").remove();
							}
						});
					recordsSwap.after(json.oreturn);
					$("#ajax-" + recordClass + ".records .icon-bar-chart").click(function() {
						fChartSelect($(this).data());
					});
					updateNotifications("Action failed, " + json.status, json.status, ajaxnotification, records);
					$("#" + data.id).css("display", "table");
					fAjaxSorter(data.id);
						$("#" + data.id).find('.icon-hover-hint').parent().bind({
							mouseenter: function(e) {		
								$("#tooltip").remove();
								var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
								var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
								var midRel = [position[0] + mid[0], position[1] + mid[1]];
								showTooltipEle(midRel[0], midRel[1], $(this).data('text'));
							},
							mouseleave: function() {
								$("#tooltip").remove();
							}
						});
				},
			// code to run if the request fails; the raw request and
			// status codes are passed to the function
			error: function( xhr, status, errorThrown ) {
					updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification, records);
				},
			// code to run regardless of success or failure
			complete: function( xhr, status ) {
					loading.remove();
				}
		});
	};
	var data, ajaxnotification, records, loading, recordsSwap;
	$(".ajax-records").click(function() {
		records = $(".records");
		ajaxnotification = $('#ajax-notification');
		loading = $('<div id="loading"><img src="images/loading.gif" alt="Loading" title="Loading" /></div>');
		if (typeof(recordSwapTarget) != 'undefined') {
			iDateStart = recordSwapTarget.getAxes().xaxis.min;
			iDateEnd = recordSwapTarget.getAxes().xaxis.max;
		};
		data = $(this).data();
		recordsSwap = $("#recordswap");
		records.css("display", "none");
		switch (data.url) {
			case "includes/ajax.alarm.php":
			case "includes/ajax.alarm.single.php":
			case "includes/ajax.batch.php":	
			case "includes/ajax.batch.phase.php":	
			case "includes/ajax.trend.php":	
				if ($("#" + data.id).length <= 0 || $(this).find("span").length > 0) {
					$.ajax({
						// the URL for the request
						url: data.url,
						// the data to send (will be converted to a query string)
						data: { startdate: iDateStart, enddate: iDateEnd, department: data.department, equipment: data.equipment },
						// whether this is a POST or GET request
						type: "GET",
						// the type of data we expect back
						dataType : "json",
						beforeSend: function() {
								$("#" + data.id).remove();
								updateNotifications("something", "complete", ajaxnotification, records);
								recordsSwap.after(loading);
							},
						// code to run if the request succeeds;
						// the response is passed to the function
						success: function( json ) {
								recordsSwap.after(json.oreturn);
								//tablesorter
								updateNotifications("Action failed, " + json.status, json.status, ajaxnotification, records);
								$(".records .icon-bar-chart").click(function() {
									fChartSelect($(this).data());
								});
								switch (data.url) {
									case "includes/ajax.alarm.php":
									case "includes/ajax.batch.php":
										
										$("#ajax-alarm .icon-table, #ajax-batch .icon-table").click(function() {
											fRecordDetail(this);
											return false;
										});
										break;
									case "includes/ajax.trend.php":	
										$(".listheader").click(function() {
											var jThis = $(this);
											var listItem = jThis.next("ul");
											if (listItem.css("display") == "none") {
												listItem.css("display", "block");
												jThis.children(".icon-caret-right").css("display", "none");
												jThis.children(".icon-caret-down").css("display", "inline");
											} else {
												listItem.css("display", "none");
												jThis.children(".icon-caret-right").css("display", "inline");
												jThis.children(".icon-caret-down").css("display", "none");
											};
											return false;
										});
										break;
								};
							$("#" + data.id).css("display", "table");
							fAjaxSorter(data.id);
								$("#" + data.id).find('.icon-hover-hint').parent().bind({
									mouseenter: function(e) {		
										$("#tooltip").remove();
										var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
										var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
										var midRel = [position[0] + mid[0], position[1] + mid[1]];
										showTooltipEle(midRel[0], midRel[1], $(this).data('text'));
									},
									mouseleave: function() {
										$("#tooltip").remove();
									}
								});
							},
						// code to run if the request fails; the raw request and
						// status codes are passed to the function
						error: function( xhr, status, errorThrown ) {
								updateNotifications( xhr.status + ": " + xhr.statusText, xhr.status, ajaxnotification, records);
							},
						// code to run regardless of success or failure
						complete: function( xhr, status ) {
								loading.remove();
							}
					});
				} else {
					$("#" + data.id).css("display", "table");
				};
				break;
			default:
				records.not(".ajax").css("display", "table");
				break;
		};
		return false;
	});
	$('.table-row-button').click(function() {
		fRowButton(this);
		return false;
	});
	$('#help a[href="#"]').click(function() {
		var clickedHelp = $(this).next();
		if (clickedHelp.css('display') == 'block') {
			clickedHelp.css('display', 'none');
		} else {
			clickedHelp.css('display', 'block');
		};
		return false;
	});
	$('.icon-hover-hint').parent().bind({
		mouseenter: function(e) {			
			$("#tooltip").remove();
			var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
			var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
			var midRel = [position[0] + mid[0], position[1] + mid[1]];
			/*var windowWid = [$(window).width(), $(window).height()];
			var windowRel = [windowWid[0] / 2, windowWid[1] / 2];
			var orientation = ['left', 'top'];
			if (midRel[0] < windowRel[0]) {
				orientation[0] = 'right';
			};
			if (midRel[1] < windowRel[1]) {
				orientation[1] = 'bottom';
			};*/
			showTooltipEle(midRel[0], midRel[1], $(this).data('text'));
		},
		mouseleave: function() {
			$("#tooltip").remove();
		}
	});
	$('.trend-button').bind({
		mouseenter: function(e) {
			$("#tooltip").remove();
			var position = [$(this).offset().left - $(window).scrollLeft(), $(this).offset().top - $(window).scrollTop()];
			var mid = [$(this).outerWidth(true) / 2, $(this).outerHeight(true) / 2];
			var midRel = [position[0] + mid[0], position[1] + mid[1]];
			/*var windowWid = [$(window).width(), $(window).height()];
			var windowRel = [windowWid[0] / 2, windowWid[1] / 2];
			var orientation = ['left', 'top'];
			if (midRel[0] < windowRel[0]) {
				orientation[0] = 'right';
			};
			if (midRel[1] < windowRel[1]) {
				orientation[1] = 'bottom';
			};*/
			showTooltipEle(midRel[0], midRel[1], $.ucwords($(this).prop("id")));
		},
		mouseleave: function() {
			$("#tooltip").remove();
		}
	});
	var navIntent;
	var navCloseTime = 2000;
	function fCloseNav(time, clicked) {
		navIntent = setTimeout(function() {
			if ($(clicked).children().attr('class') != 'icon-briefcase icon-hover-hint icon-large') {
				$('#subnav').css('display', 'none');
				$('.icon-briefcase').parent().removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-calendar-empty icon-hover-hint icon-large') {
				$('#datetimepicker').css('display', 'none');
				$('.icon-calendar-empty').parent().removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-question-sign icon-hover-hint icon-large') {
				$('#help').css('display', 'none');
				$('.icon-question-sign').parent().removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-search icon-hover-hint icon-large') {
				$('#search').css('display', 'none');
				$('.icon-search').parent().removeClass('selected');
			};
			if (!$(clicked).parent().is('li')) {
				$('.navigation').remove();
				$('#navbar ul li a').removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-bug icon-hover-hint icon-large') {
				$('#bug').css('display', 'none');
				$('.icon-bug').parent().removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-signin icon-hover-hint icon-large') {
				$('#login-form').css('display', 'none');
				$('.icon-signin').parent().removeClass('selected');
			};
			if ($(clicked).children().attr('class') != 'icon-download-alt icon-hover-hint icon-large') {
				$('#downloadmenu').css('display', 'none');
				$('.icon-download-alt').parent().removeClass('selected');
			};
		}, time);
	};
	var elemFocus = '';
	$('#navbar, #datetimepicker, #help, #search, #bug, #subnav, #downloadmenu, #login-form').mouseleave(function() {
		$('input, textarea, select, option').focusin(function() {
			elemFocus = $(this).prop("tagName");
		} );
		$('input, textarea, select, option').focusout(function() {
			elemFocus = '';
		} );
		if (elemFocus == '') {
			fCloseNav(navCloseTime, this);
		};
	} );
	$('#navbar, #datetimepicker, #help, #search, #bug, #subnav, #downloadmenu, #login-form').mouseenter(function() {
		clearTimeout(navIntent);
	} );
	$('input, textarea, iframe, select, option').focusin(function() {
		elemFocus = $(this).prop("tagName");
	} );
	$('input, textarea, iframe, select, option').focusout(function() {
		elemFocus = '';
	} );
	$(window).scroll(function() {
		fCloseNav(0, this);
	} );
	$('#navbar ul li a[href="#"]').click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$(this).parent().parent().parent().nextAll('.navigation').remove();
			$(this).parent().parent().nextAll('.navigation').remove();
		} else {
			$(this).parent().parent().find('.selected').removeClass('selected');
			$(this).addClass('selected');
			$(this).parent().parent().nextAll('.navigation').remove();
			$(this).parent().parent().parent().nextAll('.navigation').remove();
			$(this).next().clone(true).appendTo('#navbar').addClass('navigation');
		};
		return false;
	} );
	$('.icon-briefcase').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#subnav').css('display', 'none');
		} else {
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			$('#subnav').css('display', 'block');
			$('#subnav').css('top', top + height + 1);
			$('#subnav').css('left', position.left);
		};
		return false;
	});
	$('.icon-download-alt').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#downloadmenu').css('display', 'none');
		} else {
			generatedLinks = '<ul>';
			$.each($('.tablesorter'), function() {
				name = $(this).data('name');
				generatedLinks += '<li>' + '<a data-name="' + name + '" href="#">Download ' + name + ' Table</a>' + '</li>';	
			});
			generatedLinks += '<ul>';
			$('#downloadmenu').html(generatedLinks);
			$('#downloadmenu a').on('click', function() {
				$('.tablesorter[data-name="' + $(this).data('name') + '"]').trigger('outputTable');
			});
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			$('#downloadmenu').css('display', 'block');
			$('#downloadmenu').css('top', top + height + 1);
			$('#downloadmenu').css('left', position.left);
		};
		return false;
	});
	$('.icon-calendar-empty').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$('#datetimepicker').css('display', 'none');
			$(this).removeClass('selected');
		} else {
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			$('#datetimepicker').css('display', 'block');
			$('#datetimepicker').css('top', top + height + 1);
			$('#datetimepicker').css('left', position.left);
		};
		return false;
	});
	$('#icon-questionhints').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#help').css('display', 'none');
		} else {
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			var width = $(this).outerWidth(true);
			var right = $(window).width() - (position.left + width);
			$('#help').css('display', 'block');
			$('#help').css('top', top + height + 1);
			$('#help').css('right', right);
		};
		return false;
	});
		$('.icon-signin').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#login-form').css('display', 'none');
		} else {
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			var width = $(this).outerWidth(true);
			var right = $(window).width() - (position.left + width);
			$('#login-form').css('display', 'block');
			$('#login-form').css('top', top + height + 1);
			$('#login-form').css('right', right);
			$('#login-form input[name="user"]').focus();
		};
		return false;
	});
		$('.icon-bug').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#bug').css('display', 'none');
		} else {
			$(this).addClass('selected');
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			var width = $(this).outerWidth(true);
			var right = $(window).width() - (position.left + width);
			$('#bug').css('display', 'block');
			$('#bug').css('top', top + height + 1);
			$('#bug').css('right', right);
		};
		return false;
	});
	$("#navbug").click(function() {
		var bValid = true;
		$("input, select").removeClass( "ui-state-error" );
		bValid = bValid && checkOptionSelected($('#navpriority'), 'Priority must be selected.');
		bValid = bValid && checkTextBox($('#bugbody'), 'The Comment must contain text.');
		if (bValid == false) {
			return false;
		};
	});
	$('.icon-search').parent().click(function() {
		fCloseNav(0, this);
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
			$('#search').css('display', 'none');
		} else {
			var top = $(this).scrollTop();
			var position = $(this).offset();
			var height = $(this).outerHeight(true);
			$('#search').css('display', 'block');
			$('#search').css('top', top + height + 1);
			$('#search').css('left', position.left);
			$('#search input[type="text"]').focus();
		};
		return false;
	});
	$(".content #comment").sceditor({
		plugins: 'html',
		style: "js/sceditor/jquery.sceditor.default.min.css",
		toolbarExclude: "font,cut,copy,paste,pastetext,emoticon,youtube,date,time,ltr,rtl,print,source,color,size,removeformat",
		emoticonsEnabled: false,
		resizeEnabled: false,
		id:"bodybody"
	});
	$("#bug textarea").sceditor({
		plugins: 'html,scnavintent',
		style: "js/sceditor/jquery.sceditor.default.min.css",
		toolbarExclude: "font,cut,copy,paste,pastetext,emoticon,youtube,date,time,ltr,rtl,print,source,color,size,removeformat,table,image,email,link,unlink,strike,subscript,superscript,maximize,horizontalrule",
		emoticonsEnabled: false,
		resizeEnabled: false,
		id:"bugbody"
	});
	$(".content #comment").focus(function() {
		elemFocus = 'TEXTAREA';
	});
	$(".content #comment").blur(function() {
		elemFocus = '';
	});
});