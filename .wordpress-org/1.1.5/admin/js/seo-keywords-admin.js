/**
 * All of the code for your admin-facing JavaScript source
 * should reside in this file.
 *
 * Note: It has been assumed you will write jQuery code here, so the
 * $ function reference has been prepared for usage within the scope
 * of this function.
 *
 * This enables you to define handlers, for when the DOM is ready:
 *
 * $(function() {
 *
 * });
 *
 * When the window is loaded:
 *
 * $( window ).load(function() {
 *
 * });
 *
 * ...and/or other possibilities.
 *
 * Ideally, it is not considered best practise to attach more than a
 * single DOM-ready or window-load handler for a particular page.
 * Although scripts in the WordPress core, Plugins and Themes may be
 * practising this, we should strive to set a better example in our own work.
 */

function Seo_Keywords_isGutenbergActive() {
	return document.body.classList.contains( 'block-editor-page' );
}

function Seo_Keywords_UpdateContentLink() {
	jQuery(".editor-post-save-draft").addClass('draft-button');
	jQuery('.lds-roller').css('display', 'flex');
	jQuery('body').css('overflow', 'hidden');
	var _post_id = jQuery('input[name="post_id"]').val();
	var _keyword = jQuery('input[name="keyword"]').val();
	if( _post_id == ""){
		jQuery(".editor-post-save-draft").trigger( "click" );
		jQuery("input#save-post").click().prop('disabled', false);
	}

	var _post_id = jQuery('input[name="post_id"]').val();
	Seo_Keywords_SavePost( _post_id );
}

function Seo_Keywords_SavePost( _post_id ) {
	let _post_content = '';
	if( Seo_Keywords_isGutenbergActive() ) {
		const b = wp.data.select("core/editor");
		const blocks = b.getBlocks();
		for( let i = 0; i < blocks.length; i++ ) {
			_post_content += blocks[0].attributes.content;
		}
	} else {
		const _post_content_iframe = jQuery('#content_ifr').contents();
		_post_content = _post_content_iframe.find('body').html();
	}

	jQuery.ajax({
		url: SeoKeywords.ajaxUrl,
		dataType: 'json',
		type: 'POST',
		data: {
			action: 'Seo_Keywords_SavePost',
			post_id: _post_id,
			post_content: _post_content,
			nonce: SeoKeywords.Seo_Keywords_SavePostNonce,
		}
	}).fail(function(){
		console.log('Ajax request fails');
	}).done(function( data ){
		console.log( data );
		Seo_Keywords_UpdateContentAjax();
	});
}

function Seo_Keywords_UpdateContentAjax(){
	var _post_id = jQuery('input[name="post_id"]').val();
	var _keyword = jQuery('input[name="keyword"]').val();


	var pathname = window.location.href;
	var splitUrl = pathname.split('?');
	if(splitUrl[1] != null){
		var pIDUrl = splitUrl[1].split('&');
		var _post_id_url = pIDUrl[0].split('=');
		var _post_id = _post_id_url[1];

		var data = {
			action: 'Seo_Keywords_FolderContents',
			keyword : _keyword,
			post_id : _post_id,
			nonce: SeoKeywords.Seo_Keywords_FolderContentsNonce,
		};

		jQuery.ajax({
			url: SeoKeywords.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			context: this,
			data: data
		}).fail(function(){
			console.log('Ajax request fails')
		}).done(function(response){
			console.log('done');
			console.log( response );

			if( response.status == -1 || response.status == -3 || response.status == -4 ){
				window.location.href = window.location.href + '&response_status=' + response.status + '&google_error=' + response.message;
				return;
			}

			if( response.status == -2 ) {
				window.location.href = SeoKeywords.Seo_Keywords_Backend_Url + 'searchconsole?api_key=' + SeoKeywords.api_key + '&domain=' + SeoKeywords.Seo_Keywords_Site_Url + '&remote_server_uri=' + SeoKeywords.Seo_Keywords_Remote_Server_Uri;
				return;
			}

			jQuery('.lds-roller').hide();
			jQuery('body').css('overflow', 'scroll');

			/**
			 * Update metabox
			 */
			const seo_links_keywords_impressions = response.seo_links_keywords_impressions;
			const seo_links_keywords_clicks = response.seo_links_keywords_clicks;
			const seo_links_keywords_position = response.seo_links_keywords_position;
			const seo_links_keywords_filtered = response.seo_links_keywords_filtered;
			const seo_links_keywords = response.seo_links_keywords;
			const seo_links_keywords_related = response.seo_links_keywords_related;

			<!-- SEO Keywords -->
			let seo_keywords = [];
			for( let slkf in seo_links_keywords_filtered ) {
				seo_keywords[ seo_links_keywords_filtered[ slkf ] ] = seo_links_keywords_impressions[ seo_links_keywords_filtered[ slkf ] ];
			}

			let seo_link_keywords_html = `
				<input type="text" id="seo_keywords_input" onkeyup="seoKeywordResearch('seo_keywords')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
				<table style="margin: 8px 0;">
					<tr>
						<th scope="row" style="width:55%;cursor: pointer;">Keyword</th>
						<th scope="row" style="cursor: pointer;">Impr.</th>
						<th scope="row" style="cursor: pointer;">Clicks</th>
						<th scope="row" style="cursor: pointer;">Pos.</th>
					</tr>`;

			seo_keywords = [];
			for( let slk in seo_links_keywords ) {
				seo_keywords[ seo_links_keywords[ slk ] ] = seo_links_keywords_impressions[ seo_links_keywords[ slk ] ];
			}

			q_sorted = [];
			Seo_Keywords_bySortedValue( seo_keywords, function(key, value) {
				q_sorted[key] = value;
			});
			seo_keywords = q_sorted;

			let seo_keywords_index = 0;
			for( let sk in seo_keywords ) {
				seo_keywords_index++;
				seo_link_keywords_html += `
					<tr class="seo_keywords">
						<td>
							`+ Seo_Keywords_stripslashes( sk ) +`
						</td>
						<td style="text-align: center;">
							`+ seo_keywords[ sk ] +`
						</td>
						<td style="text-align: center;">
							` + seo_links_keywords_clicks[ sk ] + `						
						</td>
						<td style="text-align: center;">
							` + seo_links_keywords_position[ sk ] + `						
						</td>
					</tr>`;
			}

			seo_link_keywords_html += `</table>`;

			let seo_keywords_related = [];
			let seo_link_keywords_related_html = `
				<input type="text" id="seo_keywords_related_input" onkeyup="seoKeywordResearch('seo_keywords_related')" placeholder="Search for keyword.." style="margin-top: 16px;width: 100%;" />
				<table style="margin: 8px 0;">
					<tr>
						<th scope="row" style="width:55%;cursor: pointer;">Keyword</th>
						<th scope="row" style="cursor: pointer;">Impr.</th>
						<th scope="row" style="cursor: pointer;">Clicks</th>
						<th scope="row" style="cursor: pointer;">Pos.</th>
					</tr>`;

			for( let slk in seo_links_keywords_related ) {
				seo_keywords_related[ seo_links_keywords_related[ slk ] ] = seo_links_keywords_impressions[ seo_links_keywords_related[ slk ] ];
			}

			q_sorted = [];
			Seo_Keywords_bySortedValue( seo_keywords_related, function(key, value) {
				q_sorted[key] = value;
			});
			seo_keywords_related = q_sorted;
			let seo_keywords_related_index = 0;
			for( let sk in seo_keywords_related ) {
				seo_keywords_related_index++;
				seo_link_keywords_related_html += `
					<tr class="seo_keywords">
						<td>
							`+ Seo_Keywords_stripslashes( sk ) +`
						</td>
						<td style="text-align: center;">
							`+ seo_keywords_related[ sk ] +`
						</td>
						<td style="text-align: center;">
							` + seo_links_keywords_clicks[ sk ] + `						
						</td>
						<td style="text-align: center;">
							` + seo_links_keywords_position[ sk ] + `						
						</td>
					</tr>`;
			}

			seo_link_keywords_related_html += `</table>`;


			if( seo_keywords_index == 0 ){
				// No keywords found
				seo_link_keywords_html += `<div class="notice notice-error is-dismissible"><p>No keywords found ranking for this article yet. try again in a few days</p></div>`;
				seo_link_keywords_html += `
				<div id="seo_keywords_no_keywords_found" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">No keywords found</h5>
                            </div>
                            <div class="modal-body">
                                <p>We did not find any keyword ranking in Google for this url. try waiting a few days or try reindexing the page in Google.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="jQuery('#seo_keywords_no_keywords_found').modal('hide');">Close</button>
                            </div>
                        </div>
                    </div>
                </div>`;
			}

			document.getElementById('seo_keywords').innerHTML = seo_link_keywords_html;
			document.getElementById('seo_keywords_related').innerHTML = seo_link_keywords_related_html;

			if( seo_keywords_index == 0 ) {
				setTimeout(function () {
					jQuery('#seo_keywords_no_keywords_found').modal('show');
				}, 800);
			}
		});
	}
}

function Seo_Keywords_bySortedValue(obj, callback, context) {
	var tuples = [];

	for (var key in obj) tuples.push([key, obj[key]]);

	tuples.sort(function(a, b) {
		return a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : 0
	});

	var length = tuples.length;
	while (length--) callback.call(context, tuples[length][0], tuples[length][1]);
}

function Seo_Keywords_stripslashes(str) {
	return str.replace(/\\'/g,'\'').replace(/\"/g,'"').replace(/\\\\/g,'\\').replace(/\\0/g,'\0');
}

function seoKeywordResearch( id ) {
	// Declare variables
	var input, filter, table, tr, td, i, txtValue;
	input = document.getElementById(id + "_input");
	filter = input.value.toUpperCase();
	table = table = jQuery('#' + id).find('table')[0];
	tr = table.getElementsByTagName("tr");

	// Loop through all table rows, and hide those who don't match the search query
	for (i = 0; i < tr.length; i++) {
		td = tr[i].getElementsByTagName("td")[0];
		if (td) {
			txtValue = td.textContent || td.innerText;
			if (txtValue.toUpperCase().indexOf(filter) > -1) {
				tr[i].style.display = "";
			} else {
				tr[i].style.display = "none";
			}
			if( tr[i].className == 'internal_links_keywords_filtered' ) {
				tr[i].style.display = "none";
			}
		}
	}
}

const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
		v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
)(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

// do the work...
document.querySelectorAll('.seo_keywords th, .seo_keywords_related th').forEach(th => th.addEventListener('click', (() => {
	const table = th.closest('table');
	Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
		.sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
		.forEach(tr => table.appendChild(tr) );
})));

(function( $ ) {
	'use strict';

})( jQuery );
