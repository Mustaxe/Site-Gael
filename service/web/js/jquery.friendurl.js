/*
 * jQuery FriendURL plugin 1.7
 * 
 * Bugfixed by: Vitaliy Stepanenko (http://nayjest.ru)    
 */

(function ($, undefined) {
	
	var cyrillic = [
		"Ð°", "Ð±", "Ð²", "Ð³", "Ð´", "Ðµ", "Ð¶", "Ð·", "Ð¸", "Ð¹", "Ðº", "Ð»", "Ð¼", "Ð½", "Ð¾",
		"Ð¿", "Ñ€", "Ñ", "Ñ‚", "Ñƒ", "Ñ„", "Ñ…", "Ñ†", "Ñ‡", "Ñˆ", "Ñ‰", "ÑŠ", "ÑŒ", "ÑŽ", "Ñ",
		"Ð", "Ð‘", "Ð’", "Ð“", "Ð”", "Ð•", "Ð–", "Ð—", "Ð˜", "Ð™", "Ðš", "Ð›", "Ðœ", "Ð", "Ðž", 
		"ÐŸ", "Ð ", "Ð¡", "Ð¢", "Ð£", "Ð¤", "Ð¥", "Ð¦", "Ð§", "Ð¨", "Ð©", "Ðª", "Ð¬", "Ð®", "Ð¯",
		"Ð‡", "Ñ—", "Ð„", "Ñ”", "Ð«", "Ñ‹", "Ð", "Ñ‘",
		"Ä±", "Ä°", "ÄŸ", "Äž", "Ã¼", "Ãœ", "ÅŸ", "Åž", "Ã¶", "Ã–", "Ã§", "Ã‡",
		"Ã", "Ã¡", "Ã‚", "Ã¢", "Ãƒ", "Ã£", "Ã€", "Ã ", "Ã‡", "Ã§", "Ã‰", "Ã©", "ÃŠ", "Ãª", "Ã", 
		"Ã­", "Ã“", "Ã³", "Ã”", "Ã´", "Ã•", "Ãµ", "Ãš", "Ãº",
		"ç",
		"á","ã","â","à",
		"é","ê","è",
		"í","ì","î",
		"õ","ò","ó","ô",
		"ú","ù","û",
		"ñ"		
		];

	var latin = [
		"a", "b", "v", "g", "d", "e", "zh", "z", "i", "y", "k", "l", "m", "n", "o", 
		"p", "r", "s", "t", "u", "f", "h", "ts", "ch", "sh", "sht", "a", "y", "yu", "ya",
		"A", "B", "B", "G", "D", "E", "Zh", "Z", "I", "Y", "K", "L", "M", "N", "O", 
		"P", "R", "S", "T", "U", "F", "H", "Ts", "Ch", "Sh", "Sht", "A", "Y", "Yu", "Ya",
		"I", "i", "Ye", "ye", "I", "i", "Yo", "yo",
		"i", "I", "g", "G", "u", "U", "s", "S", "o", "O", "c", "C",
		"A", "a", "A", "a", "A", "a", "A", "a", "C", "c", "E", "e", "E", "e", "I",
		"i", "O", "o", "O", "o", "O", "o", "U", "u",
		"c",
		"a","a","a","a",
		"e","e","e",
		"i","i","i",
		"o","o","o","o",
		"u","u","u",
		"n"
	];
	
	var string = '';
	
	function convert (text) {
		string = str_replace(cyrillic, latin, text);
		return string;
	}
		
	function str_replace (search, replace, subject, count) {	    
	
	    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
	            f = [].concat(search),
	            r = [].concat(replace),
	            s = subject,
	            ra = r instanceof Array, sa = s instanceof Array;
	    s = [].concat(s);
	    if (count) {
	        this.window[count] = 0;
	    }
	
	    for (i=0, sl=s.length; i < sl; i++) {
	        if (s[i] === '') {
	            continue;
	        }
	        for (j=0, fl=f.length; j < fl; j++) {
	            temp = s[i]+'';
	            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
	            s[i] = (temp).split(f[j]).join(repl);
	            if (count && s[i] !== temp) {
	                this.window[count] += (temp.length-s[i].length)/f[j].length;
	            }
	        }
	    }
	    return sa ? s : s[0];
	}
	
	function Friendurl () {
		this.defaults = {
			divider : '-',
			transliterate: true
		};
	}
	
	Friendurl.prototype = {
		_initFriendurl: function (target, options) {
			var self = this;
			$(target).keyup(function () {
				options = $.extend(self.defaults, options);

				var url = $(this).val();
				
				if (options.transliterate) {
					//alert(url.toLowerCase());
    				url = convert(url.toLowerCase());
    			}

				url = url
    				.toLowerCase() // change everything to lowercase
    				.replace(/^\s+|\s+$/g, "") // trim leading and trailing spaces		
    				.replace(/[_|\s]+/g, "-") // change all spaces and underscores to a hyphen
    				.replace(/[^a-z\u0400-\u04FF0-9-]+/g, "") // remove all non-cyrillic, non-numeric characters except the hyphen
    				.replace(/[-]+/g, "-") // replace multiple instances of the hyphen with a single instance
    				.replace(/^-+|-+$/g, "") // trim leading and trailing hyphens
    				.replace(/[-]+/g, options.divider)				
    			;
    			
    			var $el = $('#' + options.id);

    			if ($el.length > 0) {
    				var nodeName = $el.get(0).tagName;
    				switch (nodeName) {
    					case 'INPUT':
    						$el.val(url);
    						break;
    					default:
    						$el.text(url);
    				}
    			}
			});

			$(target).change(function () {
				options = $.extend(self.defaults, options);

				var url = $(this).val();
				
				if (options.transliterate) {
					//alert(url.toLowerCase());
    				url = convert(url.toLowerCase());
    			}

				url = url
    				.toLowerCase() // change everything to lowercase
    				.replace(/^\s+|\s+$/g, "") // trim leading and trailing spaces		
    				.replace(/[_|\s]+/g, "-") // change all spaces and underscores to a hyphen
    				.replace(/[^a-z\u0400-\u04FF0-9-]+/g, "") // remove all non-cyrillic, non-numeric characters except the hyphen
    				.replace(/[-]+/g, "-") // replace multiple instances of the hyphen with a single instance
    				.replace(/^-+|-+$/g, "") // trim leading and trailing hyphens
    				.replace(/[-]+/g, options.divider)				
    			;
    			
    			var $el = $('#' + options.id);

    			if ($el.length > 0) {
    				var nodeName = $el.get(0).tagName;
    				switch (nodeName) {
    					case 'INPUT':
    						$el.val(url);
    						break;
    					default:
    						$el.text(url);
    				}
    			}
			});
		}
	};
	
	$.friendurl = new Friendurl();
	$.friendurl.version = "1.7";
	
	$.fn.friendurl = function (options) {	
		return this.each(function () {
			$.friendurl._initFriendurl(this, options);
		});
	};
})(jQuery);