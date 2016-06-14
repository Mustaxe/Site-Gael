/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	CKEDITOR.config.allowedContent = true;
	config.extraAllowedContent = 'figure';
	config.extraAllowedContent = 'iframe[*]';
	config.extraAllowedContent = 'div';
	config.extraAllowedContent = 'span(*){*}[*]';
	config.extraAllowedContent = 'p(*){*}[*]';
	config.extraAllowedContent = [
        "*[class,id]",
        "a[*]",
        "img[*]",
        "strong", "em", "small",
        "u", "s", "i", "b",
        "p", "blockquote[class,id]",
        "div[class,id,data-href]",
        "ul", "ol", "li",
        "br", "hr",
        "h1", "h2", "h3", "h4", "h5", "h6",
        "script[src,charset,async]",
        "iframe[*]", "embed[*]", "object[*]",
        "cite", "mark", "time",
        "dd", "dl", "dt",
        "table", "th", "tr", "td", "tbody", "thead", "tfoot"
    ];
	config.extraAllowedContent = 'ul(*){*}[*]';
	config.extraAllowedContent = 'li(*){*}[*]';
	config.extraAllowedContent = 'ol(*){*}[*]';	
};
