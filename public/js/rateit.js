var RateItRating;

function doRateIt() {
	// the rateit plugin as an Object
	(function() {

	RateItRatings = {

		options: {
			step: 0.1,       /* Schrittweite */
			readonly: false, /* Bewertungen zulassen */
			resetable: false /* Nicht zurücksetzbar */
		},

		// this should be called first before doing anything else
		initialize: function(options) {
		  if (typeof options == 'object' && typeof options['url'] != 'undefined')
			  this.options.url = options['url'];

		  var self = this;
		  jQuery('.rateItRating').each(function(i, element) {
			  self.initMe(element);
		  });

		  return this;
		},

		initMe: function(element) {
			var self = this;

			//Does this if the browser is NOT IE6. IE6 users don't deserve fancy ratings. >:(
			if (!Browser.Engine.trident4) {
				var el = jQuery(element);
				el.data('id', el.attr('id'));
				el.data('rateable', el.attr('rel') == 'not-rateable' ? false : true);
				el.data('wrapper', el.find('.wrapper'));
				el.data('textEl', el.find('.ratingText'));
//					el.data('offset', getPosition(element).x);
				el.data('selected', el.find('.rateItRating-selected'));
				el.data('hover', el.find('.rateItRating-hover'));

				jQuery.when(self.getBackgroundImage(el.data('wrapper'))).done(function(backgroundImageSize) {
					self.options.starwidth = backgroundImageSize[0];
					self.options.starheight = backgroundImageSize[1] / 3; // da immer drei Sterne "übereinander" gebraucht werden
				});
				if (self.options.starwidth === undefined || self.options.starwidth < 16) {
					self.options.starwidth = 16;
				}
				if (self.options.starheight === undefined || self.options.starheight < 16) {
					self.options.starheight = 16;
				}

				self.setBackgroundPosition(el.data('selected'), -1 * self.options.starheight);
				self.setBackgroundPosition(el.data('hover'), -1 * 2 * self.options.starheight);

				el.data('starPercent', self.getStarPercent(el.data('id')));
				el.data('ratableId', self.getRatableId(el.data('id')));
				el.data('ratableType', self.getRatableType(el.data('id')));

				// Maximalwert (=Anzahl Sterne) ermitteln
				self.options.max = self.getRatableMaxValue(el.data('id'));

				// Höhe für selected und hover einstellen
				el.data('selected').css('height', self.options.starheight);
				el.data('hover').css('height', self.options.starheight);

				// Wrapper-Größe so anpassen, dass alle Sterne angezeigt werden
				el.data('wrapper').css('width', self.options.starwidth * self.options.max);
				el.data('wrapper').css('min-width', self.options.starwidth * self.options.max);
				el.data('wrapper').css('height', self.options.starheight);

				// Breite des rateItRating-selected divs setzen
				self.fillVote(el.data('starPercent'), el);

				// Breite für rateItRating-selected div ermitteln
				el.data('currentFill', self.getFillPercent(el.data('starPercent')));

				if (el.data('rateable')) {
					el.data('wrapper').mouseenter(function(event) {
						el.data('selected').hide(500, "easeInOutQuad");
						el.data('hover').show();
						el.data('wrapper').mousemove({'el': el, 'self': self}, self.mouseCrap);
					});

					el.data('wrapper').mouseleave(function(event) {
						el.data('wrapper').unbind('mousemove');
						el.data('hover').hide();
						el.data('selected').show();
						el.data('selected').animate({width: el.data('currentFill')}, 500);
					});

					el.data('wrapper').on('click', function(event) {
						event.data = {'el': el, 'self': self};
						self.mouseCrap(event);
						el.data('oldTxt', el.data('textEl').text());
						el.data('textEl').html('');
						el.data('currentFill', el.data('newFill'));

						// falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
						if (typeof(jQuery('.mbrateItRating')) != 'undefined' && el.data('id').indexOf('mb') == 0) {
							var mbid = el.data('id');
							mbid = mbid.replace('mb', '');

							if (typeof(arrRatings) == 'object') {
								for (var ri = 0; ri < arrRatings.length; ri++) {
									if (arrRatings[ri].rateItID == mbid) {
										arrRatings[ri].rated = true;
										arrRatings[ri].width = el.data('hover').css('width');
										break;
									}
								}
							}

							if (typeof(jQuery('#' + jEscape(mbid))) != 'undefined') {
								var origWrapper = jQuery('#' + jEscape(mbid)).find('.wrapper');
								origWrapper.unbind();
								origWrapper.find('.rateItRating-selected').css('display', 'none');
								origWrapper.find('.rateItRating-hover').css('width', el.data('hover').css('width'));
								origWrapper.find('.rateItRating-hover').css('display', 'block');
							}
						} else {
							if (typeof(arrRatings) == 'object') {
								for (var ri = 0; ri < arrRatings.length; ri++) {
									if (arrRatings[ri].rateItID == el.data('id')) {
										arrRatings[ri].rated = true;
										arrRatings[ri].width = el.data('hover').css('width');
										break;
									}
								}
							}
						}

						if (!el.children('.rateItRating-send').length)
						{
							el.append('<button class="rateItRating-send">Bewertung absenden</button>');
						}
						el.find('.rateItRating-send').on('click', function() {
							el.data('wrapper').unbind();
							el.data('textEl').html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
							el.data('textEl').addClass('loading');
							var votePercent = self.getVotePercent(el.data('newFill'));
							if (self.options.url != null) {
								jQuery.ajax({
									url: self.options.url,
									type: 'post',
									data: {'vote': votePercent, 'id': el.data('ratableId'), 'type': el.data('ratableType')}
								}).done(function(data) {
									el.data('updateText')(el, data);
									el.find('.rateItRating-send').remove();
								});
							}
						});
					});
				}

				el.data('updateText', self.updateText);
			} else {
				alert("Ich bin ein IE6");
			}
		},

		fillVote: function(percent, el) {
			el.data('newFill', this.getFillPercent(percent));
			if (this.getVotePercent(el.data('newFill')) > 100) { el.data('newFill', this.getFillPercent(100)); }
			el.data('selected').css('width', el.data('newFill'));
		},

		mouseCrap: function(event) {
			var el = event.data['el'];
			var self = event.data['self'];

			var fill = event.originalEvent.layerX;
			if (!fill) {
				fill = event.originalEvent.offsetX;
			}
			var fillPercent = self.getVotePercent(fill);
			var nextStep = Math.ceil((fillPercent / 100) * self.options.max);

			var w = nextStep * self.options.starwidth;
			if (parseInt(el.data('hover').css('width')) != w) {
				el.data('selected').css('display', 'none');
				el.data('hover').css('width', Math.min(w, self.options.starwidth * self.options.max));
				el.data('hover').css('display', 'block');
			}

			var newFill = nextStep / self.options.max * 100;
			self.fillVote(newFill, el);
		},

		getStarPercent: function(id) {
			/* Format = anyStringHere-<id>-<float(currentStars)>_(scale);
			 * Example: RateItRatings-5-3_5 //Primary key id = 5, 3/5 stars. */
			var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
			if (stars != null) {
				var score = parseFloat(stars[3]);
				var scale = parseFloat(stars[4]);
				var percent =  (score / scale) * 100;
				return percent;
			} else {
				return 0;
			}
		},

		// Ermittelt die Breite des rateItRating-selected divs
		getFillPercent: function (starPercent) {
			return (starPercent / 100) * (this.options.starwidth * this.options.max);
		},

		// Aus der Breite des rateItRating-selected divs die Prozentzahl ermitteln
		getVotePercent: function(actVote) {
			var starsWidth = this.options.starwidth * this.options.max;
			var percent = (actVote / starsWidth * 100).toFixed(2);
			return percent;
		},

		getRatableId: function(id) {
			var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
			return stars != null ? stars[1] : '';
		},

		getRatableType: function(id) {
			var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
			return stars != null ? stars[2] : '';
		},

		getRatableMaxValue: function(id) {
			var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
			return stars != null ? parseInt(stars[4]) : 0;
		},

		setBackgroundPosition: function(el, pos) {
			el.css('background-position', '0% ' + pos + 'px');
		},

		getBackgroundImagePath: function(el) {
			return el.css("background-image");
		},

		getBackgroundImage: function(el) {
			var dfd = jQuery.Deferred();
			var backgroundImageSize = new Array();
			var reg_imgFile = /url\s*\(["']?(.*)["']?\)/i;
			var string = this.getBackgroundImagePath(el);
			string = string.match(reg_imgFile)[1];
			string = string.replace('\"', '');

			jQuery('<img/>')
			   .attr('src', string)
			   .load(function() {
				   backgroundImageSize.push(this.width);
				   backgroundImageSize.push(this.height);
				   dfd.resolve(backgroundImageSize);
			   });
			return dfd.promise();
		},

		updateText: function(el, text) {
			error = text.split('ERROR:')[1];
			el.data('textEl').removeClass('loading');
			if (error) { this.RateItRating.showError(el, error); return false; }
			el.data('textEl').text(text);

			// falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
			if (typeof(jQuery('.mbrateItRating')) != 'undefined' && el.data('id').indexOf('mb') == 0) {
				var mbid = el.attr('id');
				mbid = mbid.replace('mb', '');

				if (typeof(arrRatings) == 'object') {
					for (var ri = 0; ri < arrRatings.length; ri++) {
						if (arrRatings[ri].rateItID == mbid) {
							arrRatings[ri].description = text;
							break;
						}
					}
				}

				if (typeof(jQuery('#' + jEscape(mbid))) != 'undefined') {
					jQuery('#' + jEscape(mbid)).find('.ratingText').text(text);
				}
			} else {
				if (typeof(arrRatings) == 'object') {
					for (var ri = 0; ri < arrRatings.length; ri++) {
						if (arrRatings[ri].rateItID == el.data('id')) {
							arrRatings[ri].description = text;
							break;
						}
					}
				}
			}
		},

		showError: function(el, error) {
			el.data('textEl').addClass('ratingError');
			//oldTxt = el.data('textEl').text();
			el.data('textEl').text(error);
			setTimeout(function() {
				el.data('textEl').text(el.data('oldTxt'));
				el.data('textEl').removeClass('ratingError');
			}, 2000);
		}
	  };

	})(jQuery);

	jQuery(document).ready(function() {
		jQuery.ajax({
			  type: "GET",
			  url: "system/modules/rateit/public/js/jquery-ui-effects.custom.min.js",
			  dataType: "script",
			  async: false,
			  cache: true
		});
		jQuery.ajax({
			  type: "GET",
			  url: "system/modules/rateit/public/js/helper.min.js",
			  dataType: "script",
			  async: false,
			  cache: true
		});
		RateItRating = Object.create(RateItRatings).initialize({url:'system/modules/rateit/public/php/rateit-ajax.php?do=rateit'});
	});

	var jEscape = function(jquery) {
		jquery = jquery.replace(new RegExp("\\$", "g"), "\\$");
		jquery = jquery.replace(new RegExp("\~", "g"), "\\~");
		jquery = jquery.replace(new RegExp("\\[", "g"), "\\[");
		jquery = jquery.replace(new RegExp("\\]", "g"), "\\]");
		jquery = jquery.replace(new RegExp("\\|", "g"), "\\|");
		jquery = jquery.replace(new RegExp("\\.", "g"), "\\.");
		jquery = jquery.replace(new RegExp("#", "g"), "\\#");
		return jquery;
	};

}

onReadyRateIt(function() {                                               
	doRateIt();
});