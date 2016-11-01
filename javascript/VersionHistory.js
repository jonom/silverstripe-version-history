(function($) {
	/**
	 * Based on CMSPageHistoryController.js
	 *
	 * Handles related interactions between the version selection form on the
	 * left hand side of the panel and the version displaying on the right
	 * hand side.
	 */

	$.entwine('ss', function($){

		/**
		 * Class: #VersionHistoryMenu
		 *
		 * The left hand side version selection form is the main interface for
		 * users to select a version to view, or to compare two versions
		 */
		$('#VersionHistoryMenu').entwine({
			/**
			 * Function: docompare.
			 *
			 * Submits an ajax request and updates summary with result
			 */
			docompare: function() {

				var url, compare, selected, results;

				url = this.data('urlBase');
				results = $('#Root_VersionHistory #VersionComparisonSummary');

				if(!url) return false;

				compare = (this.find(":input[name=CompareMode]").is(":checked"));
				selected = this.find("table input[type=checkbox]").filter(":checked");

				if(compare) {
					if(selected.length != 2) return false;
				}
				else {
					if(selected.length != 1) return false;
				}

				url = url + selected.map(function() {
					return this.value;
				}).get().join('/');

				// Fetch comparison and update results
				results.addClass('loading').load(url, function(){
					results.removeClass('loading');
				});
			}
		});

		/**
		 * Class: #VersionHistoryMenu tr
		 *
		 * An individual row in the versions form. Selecting the row updates
		 * the edit form depending on whether we're showing individual version
		 * information or displaying comparsion.
		 */
		$("#VersionHistoryMenu tbody tr").entwine({

			/**
			 * Function: onclick
			 *
			 * Selects or deselects the row (if in compare mode). Will trigger
			 * an update of the edit form if either selected (in single mode)
			 * or if this is the second row selected (in compare mode)
			 */
			onclick: function(e) {
				var compare, selected, menu = this.parents("#VersionHistoryMenu");

				// compare mode
				compare = menu.find(':input[name=CompareMode]').attr("checked");
				selected = this.siblings(".active");

				if(compare && this.hasClass('active')) {
					this._unselect();

					return;
				}
				else if(compare) {
					// check if we have already selected more than two.
					if(selected.length > 1) {
						return alert(ss.i18n._t('ONLYSELECTTWO', 'You can only compare two versions at this time. Please deselect one version (by clicking it) before selecting another.'));
					}

					this._select();

					// if this is the second selected then we can compare.
					if(selected.length == 1) {
						menu.docompare();
					}

					return;
				}
				else {
					this._select();
					selected._unselect();

					menu.docompare();
				}
			},

			/**
			 * Function: _unselect()
			 *
			 * Unselects the row from the form selection.
			 */
			_unselect: function() {
				this.removeClass('active');
				this.find(":input[type=checkbox]").attr("checked", false);
			},

			/**
			 * Function: _select()
			 *
			 * Selects the currently matched row in the form selection
			 */
			_select: function() {
				this.addClass('active');
				this.find(":input[type=checkbox]").attr("checked", true);
			}

		});
	});
})(jQuery);
