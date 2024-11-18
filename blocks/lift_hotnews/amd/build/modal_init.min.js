define(['jquery', 'core/modal_factory', 'core/modal_events'], function($, ModalFactory, ModalEvents) {
    return {
        init: function() {
            $(document).ready(function() {
                // Attach click event to modal links
                $('a[data-modal-content]').on('click', function(e) {
                    e.preventDefault();

                    // Get the content for the modal
                    var modalTitle = $(this).data('modal-title');
                    var modalContent = $(this).data('modal-content');

                    // Create and show the modal
                    ModalFactory.create({
                        title: modalTitle,
                        body: modalContent
                    }).then(function(modal) {
                        modal.show();

                        // Listen for modal close event
                        modal.getRoot().on(ModalEvents.hidden, function() {
                            modal.destroy();
                        });
                    }).catch(function(error) {
                        console.error('Error creating modal:', error);
                    });
                });
            });
        }
    };
});
