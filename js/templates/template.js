;(function($, window){
	'use strict';

	function Template(){}

	Template.prototype.make = function make(data){

		if(!this.template){
			return '';
		}

		var result = this.template;


		for (var prop in data) {
			if( data.hasOwnProperty( prop ) && typeof data[prop] === 'string' ) {
				result = result.replace('{{' + prop.toLowerCase() + '}}', data[prop]);
			}
		}


		return result
	}

	window.Template = Template;

})(jQuery, window);
