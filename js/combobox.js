;(function($, window){
	'use strict';

	function ComboBox(container) {
		this.container = container;
		this.window = $(window);
		this.body = $('body');
		this.field = this.container.find('.combobox-field');
		this.menuWork = this.body.find('.btn-work, #btn-work');
		this.menu = this.container.find('.combobox-field');
		this.content = this.container.find('.combobox-content');
		this.contentScroll = this.content.find('.combobox-content-data');
		this.links = this.content.find('a');
	}

	ComboBox.prototype.init = function init(){
		this.addEventListener();
		this.contentScroll.mCustomScrollbar({
			theme:"rounded-dark"
		});
	}
	/*
		Listeners
	*********************************************************************/
	ComboBox.prototype.addEventListener = function addEventListener(){
		this.body.on('click' ,  this.onClickBody.bind(this));
		this.container.on('click' ,  this.onClickContainer.bind(this));
		this.menuWork.on('click' ,  this.onClickMenuWork.bind(this));
		this.links.on('click' ,  this.onClickLinks.bind(this));
	}

	ComboBox.prototype.onClickBody = function onClickBody(){
		this.close();
	}

	ComboBox.prototype.onClickContainer = function onClickContainer(e){
		e.preventDefault();
		e.stopPropagation();


		if( this.content.hasClass('open') ) {

			this.close();

		} else {

			this.open();

		}
	}

	ComboBox.prototype.onClickMenuWork = function onClickMenuWork(e){
		e.preventDefault();
		e.stopPropagation();
		var target = $('.see-all');

		this.activeLink(target);
		this.setDataField(target);
		this.close();

		this.window.trigger('work.filter' , [target.data('id-categoria')]);
	}

	ComboBox.prototype.onClickLinks = function onClickLinks(e){
		e.preventDefault();
		e.stopPropagation();
		var target = $(e.currentTarget);

		this.activeLink(target);
		this.setDataField(target);
		this.close();

		this.window.trigger('work.filter' , [target.data('id-categoria')]);
	}

	/*
		Animation
	*********************************************************************/
	ComboBox.prototype.close = function close(){
		this.content.removeClass('open');
	}

	ComboBox.prototype.open = function open(){
		this.content.addClass('open');
	}

	/*
		States
	*********************************************************************/

	ComboBox.prototype.activeLink = function activeLink(elem){
		this.links.removeClass('active');
		$(elem).addClass('active');
	}

	ComboBox.prototype.setDataField = function setDataField(elem){
		var label = elem.html();

		if(label === 'Ver Todos'){
			label = 'Todos';
		}

		this.field.html( label );
	}

	window.ComboBox = ComboBox;

})(jQuery, window);
