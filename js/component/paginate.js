;(function($, window){
	'use strict';

	function Paginate(data){
		this.currentPage = 1;
		this.pages = 1;
		this.range = 1;
	}

	Paginate.prototype.hasMore = function hasMore(){
		return  this.currentPage != this.pages;
	}

	Paginate.prototype.hasLess = function hasLess(){
		return  this.currentPage != 0;
	}

	Paginate.prototype.frist = function frist(){
		return (this.currentPage === 1 || this.pages === 1);
	}

	Paginate.prototype.last = function last(){
		return (this.currentPage === this.pages);
	}

	Paginate.prototype.next = function next(){
		if(!this.hasMore()){
			return false;
		}

		this.currentPage++;
		return this.getPage(this.currentPage);
	}

	Paginate.prototype.previous = function previous(){
		if(!this.hasLess()){
			return false;
		}

		this.currentPage--;
		return this.getPage(this.currentPage);
	}

	Paginate.prototype.getPage = function getPage(index){
		var strat = ( index - 1 ) *  this.range,
			end = strat + this.range;

		if(end > this.data.length){
			end = this.data.length;
		}

		return this.data.slice(strat , end);
	}

	Paginate.prototype.reset = function reset(){
		this.currentPage = 0;

		return this;
	}

	Paginate.prototype.setData = function setData(data){
		var length;

		if(!data){
			return  false;
		}

		this.data = data;

		length = this.data.length || (Object.keys(this.data)).length;

		this.pages = Math.ceil( length / this.range );

		this.reset();

		return this;
	}

	window.Paginate = Paginate;

})(jQuery, window);
