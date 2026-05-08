/*
 * CKFinder - Sample Plugins
 * ==========================
 * http://cksource.com/ckfinder
 * Copyright (C) 2007-2022, CKSource Holding sp. z o.o.. All rights reserved.
 *
 * This file and its contents are subject to the MIT License.
 * Please read the LICENSE.md file before using, installing, copying,
 * modifying or distribute this file or part of its contents.
 */

CKFinder.define( [ 'underscore', 'backbone', 'marionette', 'doT' ], function( _, Backbone, Marionette, doT ) {
	'use strict';
	return {
		init: function( finder ) {
			var messageModel = new Backbone.Model({
				leftMessage: '',
				rightMessage: ''
			});
			var statusBarView = new Marionette.ItemView( {
				tagName: 'p',
				template: doT.template('<span class="left-statusBarView">{{= it.leftMessage }}</span><span class="right-statusBarView">{{= it.rightMessage }}</span>'),
				model: messageModel,
				modelEvents: {
					'change': 'render'
				}
			} );

			finder.on( 'page:create:Main', function() {
				finder.request( 'statusBar:create', {
					name: 'MyStatusBar',
					page: 'Main',
					label: 'My Status Bar'
				} );
				finder.request( 'statusBar:addRegion', {
					id: 'my-status-bar-region',
					name: 'MyStatusBar'
				} );
				finder.request( 'statusBar:showView', {
					region: 'my-status-bar-region',
					name: 'MyStatusBar',
					view: statusBarView
				} );
				finder.on( 'files:selected', function( evt ) {
					var selectedFiles = evt.data.files;
					if ( !selectedFiles.length ) {
						var folder = evt.finder.request( 'folder:getActive' );
						var filesCount = evt.finder.request('files:getCurrent').length;
						messageModel.set({
							leftMessage: 'Thư mục "' + finder.util.escapeHtml( folder.get( 'name' ) ) + '" hiện có ' + filesCount + ' tập tin',
							rightMessage: '* Lưu ý: Việc xóa tập tin sẽ không thể khôi phục.'
						});
					} else if ( selectedFiles.length === 1 ) {
						messageModel.set({
							leftMessage: 'Selected:"' + finder.util.escapeHtml( selectedFiles.at( 0 ).get( 'name' ) ),
							rightMessage: '* Lưu ý: Việc xóa tập tin sẽ không thể khôi phục.'
						});
					} else {
						messageModel.set({
							leftMessage: 'Selected "' + selectedFiles.length + '" files',
							rightMessage: '* Lưu ý: Việc xóa tập tin sẽ không thể khôi phục.'
						});
					}
				} );

				finder.on( 'folder:getFiles:after', function( evt ) {
					var filesCount = evt.finder.request( 'files:getCurrent' ).length;
					messageModel.set({
						leftMessage: 'Thư mục "' + finder.util.escapeHtml( evt.data.folder.get( 'name' ) ) + '" hiện có ' + filesCount + ' tập tin',
						rightMessage: '* Lưu ý: Việc xóa tập tin sẽ không thể khôi phục.'
					});
				} );
			} );
			this.addCss( '#my-status-bar-region p {padding: 0 1em;font-size:0.8em;font-weight:normal;display:flex;flex-flow: row wrap;width: 100%;justify-content: space-between;box-sizing: border-box;}' );
			this.addCss('.right-statusBarView {color:red;font-weight: bold;}');
		}
	};
} );
