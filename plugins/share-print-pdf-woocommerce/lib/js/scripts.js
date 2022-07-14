(function($){
"use strict";

	var imgSize = getSize();
	function getSize() {
		switch(wcspp.pagesize){
			case 'legal' :
			case 'letter' :
				return 530;
			case 'a3' :
				return 760;
			case 'a4' :
				return 515;
			default:
				return 530;
		}
	}

	function get_shares() {

		var fb = $('.wcspp-facebook:not(.wcspp-activated)');
		if ( fb.length > 0 ) {
			$.getJSON( 'http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls=' + wcspp.product_url, function( fbdata ) {
				fb.find('span').html(fbdata[0].total_count);
				fb.addClass('wcspp-activated');
			});
		}

/*		var tw = $('.wcspp-twitter:not(.wcspp-activated)');
		if ( tw.length > 0 ) {
			$.getJSON( 'http://cdn.api.twitter.com/1/urls/count.json?url=' + wcspp.product_url + '&callback=?', function( twitdata ) {
				tw.find('span').html(twitdata.count);
				tw.addClass('wcspp-activated');
			});
		}*/

		var lin = $('.wcspp-linked:not(.wcspp-activated)');
		if ( lin.length > 0 ) {
			$.getJSON( 'http://www.linkedin.com/countserv/count/share?url=' + wcspp.product_url + '&callback=?', function( linkdindata ) {
				lin.find('span').html(linkdindata.count);
				lin.addClass('wcspp-activated');
			});
		}

	}
	if ( wcspp.showcounts == 'yes' ) {
		get_shares();
	}

	var readyImgs = {};
	function getBase64FromImageUrl(url, name) {
		var img = new Image();

		img.setAttribute('crossOrigin', 'anonymous');

		img.onload = function () {
			var canvas = document.createElement("canvas");
			canvas.width =this.width;
			canvas.height =this.height;

			var ctx = canvas.getContext("2d");
			ctx.drawImage(this, 0, 0);

			var dataURL = canvas.toDataURL("image/png");

			readyImgs[name] = dataURL;

		};

		img.src = url;
	}

	$.fn.print = function() {
		if (this.size() > 1){
			this.eq( 0 ).print();
			return;
		} else if (!this.size()){
			return;
		}

		var strFrameName = ("wpspp-printer-" + (new Date()).getTime());

		var jFrame = $( "<iframe name='" + strFrameName + "'>" );

		jFrame
			.css( "width", "1px" )
			.css( "height", "1px" )
			.css( "position", "absolute" )
			.css( "left", "-999px" )
			.appendTo( $( "body:first" ) )
		;

		var objFrame = window.frames[ strFrameName ];

		var objDoc = objFrame.document;

		objDoc.open();
		objDoc.write( "<!DOCTYPE html>" );
		objDoc.write( "<html>" );
		objDoc.write( "<head>" );
		objDoc.write( "<title>" );
		objDoc.write( document.title );
		objDoc.write( "</title>" );
		objDoc.write( "<style>" + wcspp.style + "</style>" );
		objDoc.write( "</head>" );
		objDoc.write( "<body>" );
		objDoc.write( this.html() );
		objDoc.write( "</body>" );
		objDoc.write( "</html>" );
		objDoc.close();

		objFrame.focus();
		objFrame.print();

		setTimeout(
			function(){
			jFrame.remove();
		},
		(60 * 1000)
		);
	};

	var pdfData = {};

	$.fn.printPdf = function(vars) {

		if ( vars.header_after == '' ) {
			pdfData.header_after = [];
		}
		else {
			pdfData.header_after = {
				text:vars.header_after,
				margin:[0,10,0,10]
			};
		}

		if ( vars.product_before == '' ) {
			pdfData.product_before = [];
		}
		else {
			pdfData.product_before = {
				text:vars.product_before,
				margin:[0,10,0,10]
			};
		}

		if ( vars.product_after == '' ) {
			pdfData.product_after = [];
		}
		else {
			pdfData.product_after = {
				text:vars.product_after,
				margin:[0,10,0,10]
			};
		}

		getBase64FromImageUrl(vars.site_logo, 'site_logo');
		getBase64FromImageUrl(vars.product_image, 'product_image');
		getBase64FromImageUrl(vars.product_img0, 'product_img0');
		getBase64FromImageUrl(vars.product_img1, 'product_img1');
		getBase64FromImageUrl(vars.product_img2, 'product_img2');
		getBase64FromImageUrl(vars.product_img3, 'product_img3');
		$('.wcspp-content img, .wcspp-content-short img').each( function() {
			getBase64FromImageUrl($(this).attr('src'), baseName($(this).attr('src')));
		});

		setTimeout( function() {
			waitForElement(vars);
		}, 333 );

	};

	function baseName(str) {
		var base = str.substring(str.lastIndexOf('/') + 1);
		if(base.lastIndexOf(".") != -1) {
			base = base.substring(0, base.lastIndexOf("."));
		}
		return base;
	}

	function getPdf(vars) {

		var site_logo = {};

		if ( vars.site_logo == '' ) {
			site_logo = {
				width:0,
				image:'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [0, 0]
			};
		}
		else {
			site_logo = {
				width:45,
				image:readyImgs.site_logo,
				fit: [37, 37]
			};
		}

		var product_img0 = {};

		if ( vars.product_img0 == '' ) {
			product_img0 = {
				width:0,
				image:'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [0, 0]
			};
		}
		else {
			product_img0 = {
				width:125,
				image:readyImgs.product_img0,
				fit: [125, 9999]
			};
		}

		var product_img1 = {};

		if ( vars.product_img1 == '' ) {
			product_img1 = {
				width:0,
				image:'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [0, 0]
			};
		}
		else {
			product_img1 = {
				width:125,
				image:readyImgs.product_img1,
				fit: [125, 9999]
			};
		}

		var product_img2 = {};

		if ( vars.product_img2 == '' ) {
			product_img2 = {
				width:0,
				image:'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [0, 0]
			};
		}
		else {
			product_img2 = {
				width:125,
				image:readyImgs.product_img2,
				fit: [125, 9999]
			};
		}

		var product_img3 = {};

		if ( vars.product_img3 == '' ) {
			product_img3 = {
				width:0,
				image:'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [0, 0]
			};
		}
		else {
			product_img3 = {
				width:125,
				image:readyImgs.product_img3,
				fit: [125, 9999]
			};
		}

		var product_img = {};

		if ( vars.product_image == '' ) {
			product_img = {
				width:270,
				image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAC0lEQVQIW2NkAAIAAAoAAggA9GkAAAAASUVORK5CYII=',
				fit: [250,9999]
			};
		}
		else {
			product_img = {
				width:270,
				image: readyImgs.product_image,
				fit: [250,9999]
			};
		}

		var convertContentHTML = pdfForElement(vars.product_content);
		var convertDescHTML = pdfForElement(vars.product_description);

		var addImgs1 = vars.product_img0 == '' ? '' : {
			alignment: 'justify',
			columns: [
				product_img0,
				product_img1
			]
		};
		var addImgs2 = vars.product_img0 == '' ? '' : {
			alignment: 'justify',
			columns: [
				product_img2,
				product_img3
			]
		};

		var prdctCats = vars.product_categories == '' ? '' : {
			text: vars.product_categories,
			style: 'meta'
		};

		var prdctTags = vars.product_tags == '' ? '' : {
			text: vars.product_tags,
			style: 'meta'
		};

		var prdctAttr = vars.product_attributes == '' ? '' : {
			text: vars.product_attributes,
			style: 'meta'
		};

		var prdctDim = vars.product_dimensions == '' ? '' : {
			text: vars.product_dimensions,
			style: 'meta'
		};
	
		var prdctWei = vars.product_weight == '' ? '' : {
			text: vars.product_weight,
			style: 'meta'
		};

		var pdfcontent = {
			content: [
				{
					alignment: 'center',
					columns: [
						site_logo,
						
					]
				},
				pdfData.header_after,
				{
					image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVQIW2NkYGD4D8QMjDAGABMaAgFVG7naAAAAAElFTkSuQmCC',
					width:imgSize,
					height:0.5,
					alignment: 'center'
				},
				pdfData.product_before,
				{
					alignment: 'justify',
					columns: [
						{
							text: vars.product_title,
							style: 'header',
							alignment: 'left'
						},
						{
							text: vars.product_price,
							style: 'header',
							alignment: 'right'
						}
					]
				},
				'\n',
				vars.product_meta,
				{
					text: vars.product_link,
					color: '#6699ff'
				},
				'\n\n',
				{
					alignment: 'justify',
					columns: [
						product_img,
						[
							addImgs1,
							addImgs2,
						]
					]
				},
				{
					text: wcspp.localization.info,
					style: 'headerProduct'
				},
				{
					image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVQIW2NkYGD4D8QMjDAGABMaAgFVG7naAAAAAElFTkSuQmCC',
					width:imgSize,
					height:0.5,
					alignment: 'center'
				},
				'\n',
				prdctCats,
				prdctTags,
				prdctAttr,
				prdctDim,
				prdctWei,
				'\n',
				convertDescHTML,
				{
					text: wcspp.localization.desc,
					style: 'headerProduct'
				},
				{
					image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVQIW2NkYGD4D8QMjDAGABMaAgFVG7naAAAAAElFTkSuQmCC',
					width:imgSize,
					height:0.5,
					alignment: 'center',
					margin: [0,0,0,5]
				},
				convertContentHTML,
				pdfData.product_after
				
			],
			styles: {
				logo: {
					minWidth: 327,
					bold: true,
					margin: [0,5,0,0]
				},
				
				header: {
					fontSize: 20,
					bold: true,
					margin: [0,5,0,0]
				},
				headerDesc: {
					fontSize: 13,
					bold: true,
					margin: [0,0,0,10]
				},
				headerProduct: {
					fontSize: 20,
					bold: true,
					margin: [0,20,0,5]
				},
				meta: {
					fontSize: 13,
					bold: true
				}
			},
			defaultStyle: {
				fontSize: 11
			},
			pageSize: wcspp.pagesize

		};

		if ( typeof loaded == 'undefined' ) {

			$.loadScript(wcspp.pdfmake, function(){
				$.loadScript(wcspp.pdffont, function(){
					var loaded = true;
					pdfMake.createPdf(pdfcontent).download(vars.site_title+' - '+vars.product_title+'.pdf');
				});
			});

		}

	}

	$.loadScript = function (url, callback) {
		$.ajax({
			url: url,
			dataType: 'script',
			success: callback,
			async: true
		});
	};

	function waitForElement(vars) {
		var checked = false;
		$.each( readyImgs, function(i, o) {
			if ( typeof o !== "undefined" ) {
				checked = true;
			}
		});

		if ( checked === true ) {
			getPdf(vars);
		}
		else {
			setTimeout( function() {
				waitForElement(vars);
			}, 333 );
		}
	}

	var ajax = 'notactive';

	function wcspp_ajax( action, product_id, type ) {

		var data = {
			action: action,
			type: type,
			product_id: product_id
		};

		return $.ajax({
			type: 'POST',
			url: wcspp.ajax,
			data: data,
			success: function(response) {
				if (response) {
					ajax = 'notactive';
				}
			},
			error: function() {
				alert('Error!');
				ajax = 'notactive';
			}
		});

	}

	$(document).on('click', '.wcspp-navigation .wcspp-print a', function() {

		if ( ajax == 'active' ) {
			return false;
		}

		ajax = 'active';
		$(this).addClass('wcspp-ajax-active');

		$.when( wcspp_ajax( 'wcspp_quickview', $(this).closest('.wcspp-navigation').data('wcspp-id'), 'print' ) ).done( function(response) {

			response = $(response);

			response.find('img[srcset]').removeAttr('srcset');

			$('body').append(response);

		});

		return false;
	});

	$(document).on('click', '.wcspp-navigation .wcspp-pdf a', function() {

		if ( ajax == 'active' ) {
			return false;
		}

		ajax = 'active';
		$(this).addClass('wcspp-ajax-active');

		$.when( wcspp_ajax( 'wcspp_quickview', $(this).closest('.wcspp-navigation').data('wcspp-id'), 'pdf' ) ).done( function(response) {

			response = $(response);

			response.find('img[srcset]').removeAttr('srcset');

			$('body').append(response);

		});

		return false;
	});

	$(document).on( 'click', '.wcspp-quickview .wcspp-quickview-close', function() {

		$('.wcspp-quickview').fadeOut(200, function() {
			$('.wcspp-ajax-active').removeClass('wcspp-ajax-active');
			$(this).remove();
		});

		return false;

	});

	$(document).on( 'click', '.wcspp-quickview .wcspp-go-print', function(e) {

		e.preventDefault();

		$('.wcspp-page-wrap').print();

		return false;

	});

	$(document).on( 'click', '.wcspp-quickview .wcspp-go-pdf', function(e) {

		e.preventDefault();

		var vars = $(this).next().data('wcspp-pdf');

		$('.wcspp-page-wrap').printPdf(vars);

		return false;

	});

	function pdfForElement(data) {
		function ParseContainer(cnt, e, p, styles) {
			var elements = [];
			var children = e.childNodes;
			if (children.length !== 0) {
				for (var i = 0; i < children.length; i++) p = ParseElement(elements, children[i], p, styles);
			}
			if (elements.length !== 0) {
				for (var i = 0; i < elements.length; i++) cnt.push(elements[i]);
			}
			return p;
		}

		function ComputeStyle(o, styles) {
			for (var i = 0; i < styles.length; i++) {
				var st = styles[i].trim().toLowerCase().split(":");
				if (st.length == 2) {
				switch (st[0]) {
					case "font-size":
					{
						o.fontSize = parseInt(st[1]);
						break;
					}
					case "text-align":
					{
						switch (st[1]) {
						case "right":
							o.alignment = 'right';
							break;
						case "center":
							o.alignment = 'center';
							break;
						}
						break;
					}
					case "font-weight":
					{
						switch (st[1]) {
						case "bold":
							o.bold = true;
							break;
						}
						break;
					}
					case "text-decoration":
					{
						switch (st[1]) {
						case "underline":
							o.decoration = "underline";
							break;
						}
						break;
					}
					case "font-style":
					{
						switch (st[1]) {
						case "italic":
							o.italics = true;
							break;
						}
						break;
					}
					case "color":
					{
						o.fillColor = st[1];
						break;
					}
				}
				}
			}
		}

		function ParseElement(cnt, e, p, styles) {
			if (!styles) styles = [];
			if (e.getAttribute) {
				var nodeStyle = e.getAttribute("style");
				if (nodeStyle) {
				var ns = nodeStyle.split(";");
				for (var k = 0; k < ns.length; k++) styles.push(ns[k]);
				}
			}

			switch (e.nodeName.toLowerCase()) {
				case "#text":
				{
					var t = {
						text: e.textContent.replace(/\n/g, "")
					};
					if (styles) ComputeStyle(t, styles);
					p.text.push(t);
					break;
				}
				case "b":
				case "strong":
				{
					ParseContainer(cnt, e, p, styles.concat(["font-weight:bold"]));
					break;
				}
				case "u":
				{
					ParseContainer(cnt, e, p, styles.concat(["text-decoration:underline"]));
					break;
				}
				case "i":
				case "em":
				{
					ParseContainer(cnt, e, p, styles.concat(["font-style:italic"]));
					break;
				}
				case "img":
				{
					p = CreateParagraph();

					var img = {
						width: imgSize,
						image: readyImgs[baseName($(e).attr('src'))]
					};
					cnt.push(img);
					break;
				}
				case "a":
				{
					var t = {
						text: '('+$(e).attr('href')+') ',
						color: '#6699ff'
					};
					ParseContainer(cnt, e, p, styles);

					p.text.push(t);

					break;
				}
				case "h1":
				case "h2":
				case "h3":
				case "h4":
				case "h5":
				case "h6":
				{
					p = CreateParagraph();
					var t = {
						text: $(e).text(),
						bold: true
					};
					switch (e.nodeName.toLowerCase()) {
						case "h1" :
							t.fontSize = 32;
							t.margin = [0,20,0,10];
						break;
						case "h2" :
							t.fontSize = 24;
							t.margin = [0,15,0,5];
						break;
						case "h3" :
							t.fontSize = 20;
							t.margin = [0,10,0,5];
						break;
						case "h4" :
							t.fontSize = 18;
							t.margin = [0,10,0,5];
						break;
						case "h5" :
							t.fontSize = 16;
							t.margin = [0,10,0,5];
						break;
						case "h6" :
							t.fontSize = 14;
							t.margin = [0,10,0,5];
						break;
					}

					cnt.push(t);
					break;
				}
				case "span":
				{
					ParseContainer(cnt, e, p, styles);
					break;
				}
				case "li":
				{
					p = CreateParagraph();
					var st = {
						stack: []
					};
					st.stack.push(p);

					ParseContainer(st.stack, e, p, styles);
					cnt.push(st);

					break;
				}
				case "ol":
				{
					var list = {
						ol: []
					};
					ParseContainer(list.ol, e, p, styles);
					cnt.push(list);

					break;
				}
				case "ul":
				{
					var list = {
						ul: []
					};
					ParseContainer(list.ul, e, p, styles);
					cnt.push(list);

					break;
				}
				case "br":
				{
					p = CreateParagraph();
					cnt.push(p);
					break;
				}
				case "table":
				{
					var t = {
						table: {
							widths: [],
							body: []
						}
					};

					ParseContainer(t.table.body, e, p, styles);

					var widths = e.getAttribute("widths");
					if (!widths) {
						if (t.table.body.length !== 0) {
							if (t.table.body[0].length !== 0)
							for (var k = 0; k < t.table.body[0].length; k++) t.table.widths.push("*");
						}
					} else {
						var w = widths.split(",");
						for (var k = 0; k < w.length; k++) t.table.widths.push(w[k]);
					}
					cnt.push(t);
					break;
				}
				case "tbody":
				{
					ParseContainer(cnt, e, p, styles);
					break;
				}
				case "tr":
				{
					var row = [];
					ParseContainer(row, e, p, styles);
					cnt.push(row);
					break;
				}
				case "th":
				{
					p = CreateParagraph();
					var st = {
						stack: []
					};
					st.stack.push(p);

					var rspan = e.getAttribute("rowspan");
					if (rspan) st.rowSpan = parseInt(rspan);
					var cspan = e.getAttribute("colspan");
					if (cspan) st.colSpan = parseInt(cspan);

					ParseContainer(st.stack, e, p, styles.concat(["font-weight:bold"]));
					cnt.push(st);
					break;
				}
				case "td":
				{
					p = CreateParagraph();
					var st = {
						stack: []
					};
					st.stack.push(p);

					var rspan = e.getAttribute("rowspan");
					if (rspan) st.rowSpan = parseInt(rspan);
					var cspan = e.getAttribute("colspan");
					if (cspan) st.colSpan = parseInt(cspan);

					ParseContainer(st.stack, e, p, styles);
					cnt.push(st);
					break;
				}
				case "div":
				case "p":
				{
					p = CreateParagraph();
					var st = {
						stack: []
					};
					st.stack.push(p);
					ComputeStyle(st, styles);
					ParseContainer(st.stack, e, p);

					cnt.push(st);

					break;
				}
				case "hr":
				{
					var splt = {
						image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVQIW2NkYGD4D8QMjDAGABMaAgFVG7naAAAAAElFTkSuQmCC',
						width:imgSize,
						height:0.5,
						alignment: 'center',
						margin:[0,10,0,10]
					};
					cnt.push(splt);
					break;
				}
				case "pre":
				{
					p = CreateParagraph();
					ParseContainer(cnt, e, p, styles);
					break;
				}
				case "blockquote":
				{
					var splt = {
						image: 'data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAEklEQVQIW2NkYGD4D8QMjDAGABMaAgFVG7naAAAAAElFTkSuQmCC',
						width:imgSize,
						height:0.5,
						alignment: 'center',
						margin:[0,10,0,10]
					};
					p = CreateParagraph();
					cnt.push(splt);
					ParseContainer(cnt, e, p, styles.concat(["font-weight:bold"]));
					cnt.push(splt);
					break;
				}
				default:
				{
					break;
				}
			}
			return p;
		}

		function ParseHtml(cnt, htmlText) {
			var html = $(htmlText.replace(/\t/g, "").replace(/\n/g, ""));
			var p = CreateParagraph();
			for (var i = 0; i < html.length; i++) ParseElement(cnt, html.get(i), p);
		}

		function CreateParagraph() {
			var p = {
				text: []
			};
			return p;
		}

		var content = [];
		ParseHtml(content, data);
		return content;
	}


})(jQuery);