//JStree directive
boxcar.directive('boxcarJstree', ['$templateCache', '$compile', function ($templateCache, $compile)
    {
        return {
            restrict: 'E',
            link: function (scope, element, attrs) {
                //When this flag is true, the move_node event is skipped


                scope.$on('ShowBoxcarTree', function (event, data)
                {
                    var to = false;
                    $('#treeSearch').keyup(function () {
                        if (to) {
                            clearTimeout(to);
                        }
                        to = setTimeout(function () {
                            var v = $('#treeSearch').val();
                            $(element).jstree(true).search(v);
                        }, 250);
                    });

                    //Destroys last instance of jstree so a new one can be created. Ideally jstree refresh should be used, but functionality of refresh has not worked
                    $(element).jstree("destroy");


                    $(element).bind("select_node.jstree", function (e, data) {
                        scope.selectInfo = data.node.original;
                        scope.$apply(attrs.storeNode);
                        //Disabled: This is a testing console log message
                        console.log("Node ID: ", data.node.id);

                    });

                    //Loads the popover template
                    var popoverContent = $templateCache.get("boxcarPopover.html");
                    //Gives the popover a controller
                    var finalContent = $compile("<div>" + popoverContent + "</div>")(scope);
                    //Options of popover
                    var options = {
                        html: true,
                        content: finalContent,
                        title: false,
                        placement: 'top',
                        trigger: 'focus'
                    };

                    //Current method for binding the popover
                    $(element).on("hover_node.jstree", function (e, data, node) {
                        if(data.node.original.popover){
                            $(".jstree-hovered").popover(options);
                        }
                    });

                    //jstree format setup
                    $(element).jstree(
                            {
                                //"state" plugin currently disabled since it does not work alongside our select node function
                                plugins: ["themes", "search", "dnd", "crrm", "ui"],
                                search: {
                                    "case_sensitive": false,
                                    //only displays matches found in search bar
                                    "show_only_matches": true,
                                    //fuzzy set to false so search looks for exact matches
                                    "fuzzy": false
                                },
                                core: {
                                    multiple: false,
                                    themes: {
                                        "theme": "default",
                                        "icons": true,
                                        "dots": false
                                    },
                                    data: data
                                }
                            }
                    );

                }, true);

            }

        };
    }]);
