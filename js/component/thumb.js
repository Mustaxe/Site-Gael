;(function($, window){
	'use strict';

	function Thumb(elem, position){

		this.elem = elem;
		this.position = position;

		this.effect = this.elem.attr("data-effect");

		this.templateThumb = new TemplateWorkThumb();
	}

	Thumb.prototype.init = function init(){
		this.isFrozen = false;
		this.addEventListener();

		return this;
	}

	/*
		Listeners
	*********************************************************************/

	Thumb.prototype.addEventListener = function addEventListener(){

		this.elem.on('mouseenter', this.onMouseEnter.bind(this));
		this.elem.on('mouseleave', this.onMouseLeave.bind(this));
		this.elem.on('click', this.onClick.bind(this));
	}

	Thumb.prototype.onMouseEnter = function onMouseEnter(){

		if(this.isFrozen){
			return false;
		}

		TweenMax.to(this.imageOver, .5, {
			opacity: 0,
			ease:Quint.easeOut
		});

		TweenMax.to(this.textContainer, .3, {
			right: 0,
			ease:Quint.easeOut,
			delay: .2
		});

		TweenMax.to(this.text, .5, {
			opacity: 1,
			ease:Quint.easeOut,
			delay: .3
		});
	}

	Thumb.prototype.onMouseLeave = function onMouseLeave(){

		if(this.isFrozen){
			return false;
		}

		TweenMax.killTweensOf(this.imageOver);
		TweenMax.killTweensOf(this.textContainer);
		TweenMax.killTweensOf(this.text);

		TweenMax.to(this.imageOver, .5, {
			opacity: 1,
			ease:Quint.easeOut
		});

		TweenMax.to(this.textContainer, .3, {
			right: -240,
			ease:Quint.easeIn
		});

		TweenMax.to(this.text, .5, {
			opacity: 0,
			ease:Quint.easeOut
		});
	}

	Thumb.prototype.onClick = function onClick(){

		if(this.isFrozen){
			return false;
		}

		window.goToByScroll(2);

		if(this.effect){
			$(window).trigger('work.open.gallery' , [this.idItem]);
		}
	}

	/*
		Change state thumb
	*********************************************************************/
	Thumb.prototype.reset = function reset(){

		TweenMax.to(this.imageOver, .2, {
			opacity: 1,
			ease:Quint.easeOut
		});
		TweenMax.to(this.textContainer, .3, {
			right: -240,
			ease:Quint.easeIn
		});
		TweenMax.to(this.text, .5, {
			opacity: 0,
			ease:Quint.easeOut
		});

		return this;

	}

	Thumb.prototype.frozen = function frozen(){
		this.imageOver.fadeOut(0);
		this.textContainer.css('right', '-240');
		this.text.fadeOut(0);

		this.isFrozen = true;

		return this;

	}

	Thumb.prototype.populate = function populate(data){
		var template  = $(this.templateThumb.make(data));

		this.elem.prepend(template);

		this.idItem =  data.id;

		this.imageOver = this.elem.find(".w-over"),
		this.textContainer = this.elem.find(".w-resume"),
		this.text = this.textContainer.find('p');

		return this;
	}

	Thumb.prototype.open = function open(delay){
		var elem = this.elem.children().first();

		delay = delay || 0;

		TweenMax.from(elem, 1.25, {
			height: 0,
			ease:Quint.easeOut,
			delay: (0.25 * delay) + 0.2,
			onComplete: this.onCompleteAnimate.bind(this),
			onCompleteParams: [elem]
		});
	}

	Thumb.prototype.onCompleteAnimate = function onCompleteAnimate(elem){
		var children = this.elem.children();

		$(elem).removeAttr('style');

		while(children.length > 1){
			children.last().remove();
			children = this.elem.children();
		}

		this.isFrozen = false;
	}

	window.Thumb = Thumb;

})(jQuery, window);
