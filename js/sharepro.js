/**
 * ownCloud - sharePro
 *
 * @author Aleksandr Tsertkov
 * @copyright 2013 Aleksandr Tsertkov tsertkov@gmail.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

$(document).ready(function(){
    // augment share with link email field with autocomplete widget
    $(document).on("focus", "#emailPrivateLink #email", function(event){
        if (!$(this).is(":data('ui-autocomplete')")) {
            autocompleteEmail($(this));
        }
    });

    /**
     * Enable ldap email autocomplete for given input
     * @param {jQuery} input
     */
    var autocompleteEmail = function(input){
        input.bind("keydown", function(event){
            // TAB will not go to another element if autocomplete is active
            if (event.keyCode === $.ui.keyCode.TAB
                && $(this).data("ui-autocomplete").menu.active)
            {
                event.preventDefault();
            }
        }).autocomplete({
            minLength: 1,
            appendTo: "#emailPrivateLink",
            source: function(request, response){
                // var path = OC.filePath('sharepro', 'ajax', 'sharepro.php');
                var path = OC.Router.generate("sharepro_emailsearch");
                var data = {query: request.term};
                $.get(path, data, function(result){
                    if (result.status === "error") {
                        OC.dialogs.alert(result.message || "LDAP search error", "Error");
                        return;
                    }

                    if (result.data && result.data.length) {
                        response(result.data);
                    }
                });
            },
            select: function(event, ui){
                var terms = input.val().split(/\s+/);

                var top = terms[terms.length - 1];
                var foundEmail = top.search('@');
                while (foundEmail  == -1 && terms.length > 0) {
                  terms.pop();         
                  if (terms.length > 0) { 
                    top = terms[terms.length - 1];
                    foundEmail = top.search('@');
                  }
                }
                
                terms.push(ui.item.value);
                terms = terms.join(" ");
                input.val(terms);
                event.preventDefault();
            }
        });

        // do not wrap automplete items text
        input.autocomplete("widget").css("white-space", "nowrap");
    };
});
