jQuery(document).ready(function ($) {
    const shortcode = $(".cmsws-shortcode");
    const tooltipText = $(".cmsws-tooltip-copy");

    if (shortcode.length) {
        shortcode.on("click", function () {
            document.execCommand("copy");
        });

        shortcode.on("copy", function (event) {
            event.preventDefault();
            const clipboardData = event.originalEvent.clipboardData;
            if (clipboardData) {
                clipboardData.setData("text/plain", shortcode.text());
            }

            tooltipText.css({ visibility: "visible", opacity: "1" });

            setTimeout(function () {
                tooltipText.css({ visibility: "hidden", opacity: "0" });
            }, 2000);
        });
    }
});

jQuery(document).ready(function ($) {
    const addWordButton = $('#add_word');
    const customWordInput = $('#cmsws_post_word');
    const customWordsInput = $('#cmsws_post_word_collection');
    const customWordsList = $('#entered_words');

    function addWordToList(newWord) {
        if (!newWord) return;

        let customWords = getExistingWords();

        if (customWords.indexOf(newWord) === -1) {
            customWords.push(newWord);
            customWordsInput.val(customWords.join(cmsws_admin_args.word_seperator));

            const listItem = $('<li></li>');
            listItem.html(`${newWord} <button class="remove-word"><span class="dashicons dashicons-trash"></span></button>`);
            customWordsList.append(listItem);

            listItem.find('.remove-word').on('click', function () {
                const word = listItem.text().trim();
                removeWord(word);
            });

            customWordInput.val('');
        } else {
            alert(cmsws_admin_args.text_word_already_in_list);
        }

        customWordInput.focus();
    }

    function removeWord(word) {
        let customWords = getExistingWords();
        const index = customWords.indexOf(word);

        if (index !== -1) {
            customWords.splice(index, 1);
            customWordsInput.val(customWords.join(cmsws_admin_args.word_seperator));
        }

        $(`#entered_words li:contains('${word}')`).remove();
    }

    function getExistingWords() { return customWordsInput.val().split(cmsws_admin_args.word_seperator);}

    addWordButton.on('click', function () {
        addWordToList(customWordInput.val().trim());
    });

    // Initialize the list from the input field on page load
    let existingWords = getExistingWords();
    customWordsInput.val([]);
    existingWords.forEach(function (word) {
        if (word.trim()) {
            addWordToList(word);
        }
    });
});



jQuery('form#post').submit(function () {
    let titleDiv = jQuery('#titlediv').find('#title');
    if (titleDiv.val().length < 1) {
        titleDiv.css('border', '1px solid red');
        jQuery('#titlewrap').after('<label style="color: red;">' + cmsws_admin_args.text_title_required + '</label>'); // Make it red according your requirement
        return false;
    }
});