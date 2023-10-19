(function ($) {

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function Cell(x, y) {
        this.x = x;
        this.y = y;
        this.solvedColorClasses = [];
        this.isSelected = false;
        this.onPath = false;
        this.letter = null;
        this.onCorrectPath = false;
    }

    Cell.prototype.willFitLetter = function (letter) {
        return this.letter === null || this.letter === letter;
    };

    Cell.prototype.randomFill = function (allowed_chars) {
        if (this.letter == null) {
            i = Math.floor(Math.random() * allowed_chars.length);
            return this.letter = allowed_chars[i];
        }
    };

    function LetterGrid(wordList = [], allowed_chars = '', directions = [], size = 10, maxWords = 10) {
        if (size === null || size === undefined ||
            maxWords === null || maxWords === undefined ||
            !Array.isArray(wordList) || wordList.length === 0) {
            throw new Error("Invalid or missing parameters for LetterGrid constructor");
        }

        this.width = size;
        this.height = size;
        this.directions = directions;

        // int cells
        this.cells = (function () {
            let result = [];
            for (let y = 0; y < this.height; y++) {
                result.push((function () {
                    let columns = [];
                    for (let x = 0; x < this.width; x++) {
                        columns.push(new Cell(x, y));
                    }
                    return columns;
                }).call(this));
            }
            return result;
        }).call(this);

        this.used_words = [];
        wordList = shuffleArray(wordList);

        // place the words
        for (const originalWord of wordList) {
            const word = originalWord.replace(/[\s-']/g, '');

            for (let count = 0; count < 3; count++) {
                // try several times to place a word
                if (!this.used_words.includes(word)) {
                    if (this._tryPlaceWord(word)) {
                        this.used_words.push(word);
                        break;
                    }
                }
            }

            if (this.used_words.length >= maxWords) {
                break;
            }
        }

        // fill the rest of the cells randomly
        this.cells.forEach(row => {
            row.forEach(cell => {
                cell.randomFill(allowed_chars);
            });
        });

        this.used_words.sort(function (a, b) {
            return b.length - a.length;
        });
    };

    LetterGrid.prototype._tryPlaceWord = function (word) {
        const dir = this.directions[Math.floor(Math.random() * (this.directions.length - 1))];
        const [dirX, dirY] = dir;

        if ((dirX !== 0 && word.length > this.width) || (dirY !== 0 && word.length > this.height)) {
            return false;
        }

        const getRandomStart = (dir, length, dimension) => {
            if (dir === -1) {
                return dimension - Math.floor(dimension - length) - 1;
            }
            return Math.floor(dimension - length) - 1;
        };

        const startX = getRandomStart(dirX, word.length, this.width);
        const startY = getRandomStart(dirY, word.length, this.height);

        const endX = startX + dirX * (word.length - 1);
        const endY = startY + dirY * (word.length - 1);

        const path = this._getPath([startX, startY], [endX, endY]);

        if (path === null) {
            return false;
        }

        for (let i = 0; i < path.length; i++) {
            cell = path[i];
            if (!cell.willFitLetter(word[i])) {
                return false;
            }
        }

        for (let i = 0; i < path.length; i++) {
            cell = path[i];
            cell.letter = word[i];
        }

        return true;
    };

    LetterGrid.prototype._getPath = function (start, end) {
        const cellPath = [];

        const startX = start[0];
        const startY = start[1];
        const endX = end[0];
        const endY = end[1];

        const diffX = endX - startX;
        const diffY = endY - startY;

        const stepX = (diffX === 0) ? 0 : diffX / Math.abs(diffX);
        const stepY = (diffY === 0) ? 0 : diffY / Math.abs(diffY);

        let currentX = startX;
        let currentY = startY;

        while (currentX >= 0 && currentY >= 0 &&
            currentX < this.width && currentY < this.height
        ) {
            cellPath.push(this.cells[currentY][currentX]);

            if (currentX === endX && currentY === endY) {
                return cellPath;
            }

            currentX += stepX;
            currentY += stepY;
        }

        return null;
    };

    LetterGrid.prototype.getHtmlTable = function() {
        const table = $('<div class="cmsws-table"></div>');
        this.cells.forEach(letterRow => {
            const row = $('<div class="cmsws-row"></div>');

            letterRow.forEach(cell => {
                const tableCell = $('<div class="cmsws-cell"></div>');
                tableCell.text(cell.letter || "");
                row.append(tableCell);
            });

            table.append(row);
        });
        return table;
    };

    LetterGrid.prototype.getUsedWordsList = function() {
        const row = $('<div id="usedWords" class="used-word-list"></div>');
        this.used_words.forEach(word => {
            const wordBox = $('<div class="word-box">' + word + '</div>');
            row.append(wordBox);
        });
        return row;
    };

    let cms_wordsearch = {

        $ws_container: $('#cms_wordsearch_container'),
        words: $('#cmsws_custom_words').val().split(cmsws_front_args.word_seperator),
        allowed_chars: $('#cmsws_allowed_chars').val().trim(),
        field_size: $('#cmsws_field_size').val().trim(),
        word_list_position: $('#cmsws_field_word_list_position').val().trim(),
        directions: Array(),
        letterGrid: null,

        init: function () {
            const MAX_LENGTH = this.field_size;

            this.$ws_container.addClass('cmsws-pos-' + this.word_list_position);

            this.words = this.words.filter(function (element) {
                return element !== "" && element.length <= MAX_LENGTH;
            });

            let dirs = $('#cmsws_directions').val().split(cmsws_front_args.word_seperator);
            dirs.forEach(direction => {
                switch (direction) {
                    case 'east':
                        this.directions.push([1, 0]);
                        break;
                    case 'south':
                        this.directions.push([0, 1]);
                        break;
                    case 'west':
                        this.directions.push([-1, 0]);
                        break;
                    case 'north':
                        this.directions.push([0, -1]);
                        break;
                    case 'southeast':
                        this.directions.push([1, 1]);
                        break;
                    case 'northeast':
                        this.directions.push([1, -1]);
                        break;
                    case 'southwest':
                        this.directions.push([-1, 1]);
                        break;
                    case 'northwest':
                        this.directions.push([-1, -1]);
                        break;
                }
            });

            this.directions = shuffleArray(this.directions);
            this.allowed_chars = shuffleArray(this.allowed_chars);
            this.createGame();
            const a = 10;
        },

        createGame: function() {
            // Remove the old container if it exists
            $('#letterGridContainer').remove();
            $('#UsedWordsContainer').remove();

            this.letterGrid = new LetterGrid(this.words, this.allowed_chars, this.directions, this.field_size);

            let newContainer = $('<div id="letterGridContainer"></div>');
            const table = this.letterGrid.getHtmlTable();
            newContainer.append(table);
            this.$ws_container.append(newContainer);

            newContainer = $('<div id="UsedWordsContainer"></div>');
            //const list = $('<div id="asd" class="used-word-list" style="width:100px;height:100px;background-color:pink"></div>');
            const list = this.letterGrid.getUsedWordsList();
            newContainer.append(list);
            this.$ws_container.append(newContainer);
        },

        wordFromPath: function (path) {
            if (!path) return '';

            return path.map(cell => cell.letter).join('');
        },


    };

    $(document).ready(function () {
        cms_wordsearch.init();

        //disable search on page (Ctrl+F)
        window.addEventListener("keydown", function (e) {
            if (e.keyCode === 114 || (e.ctrlKey && e.keyCode === 70)) {
                e.preventDefault();
            }
        })
    });
})(jQuery);

