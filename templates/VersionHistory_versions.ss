<table>
	<thead>
		<tr>
			<th class="ui-helper-hidden"></th>
			<th><% _t('CMSPageHistoryController_versions_ss.WHEN','When') %></th>
			<th><% _t('CMSPageHistoryController_versions_ss.AUTHOR','Author') %></th>
		</tr>
	</thead>

	<tbody>
		<% loop $Versions %>
		<tr id="record-$RecordID-version-$Version" class="$EvenOdd $PublishedClass<% if Active %> active<% end_if %>">
			<td class="ui-helper-hidden"><input type="checkbox" name="Versions[]" id="cms-version-{$Version}" value="$Version"<% if Active %> checked="checked"<% end_if %> /></td>
			<% with $LastEdited %>
				<td class="last-edited first-column" title="$Ago - $Nice">$Nice</td>
			<% end_with %>
			<td class="last-column"><% if $Author %>$Author.FirstName $Author.Surname.Initial<% else %><% _t('CMSPageHistoryController_versions_ss.UNKNOWN','Unknown') %><% end_if %></td>
		</tr>
		<% end_loop %>
	</tbody>
</table>
