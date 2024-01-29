(function ($) {

    function shuffleArray(array) {
        const cryptoArray = new Uint32Array(array.length);
        if (cryptoArray && cryptoArray.length > 0) {
            crypto.getRandomValues(cryptoArray);

            for (let i = array.length - 1; i > 0; i--) {
                const j = cryptoArray[i] % (i + 1);
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        return array;
    }

    /**
     *
     * @param {*} max
     * @returns a random number between 0 (inclusive) and max(exclusive)
     */
    function getRandomIndex(max) {
        const cryptoArray = new Uint32Array(1);
        crypto.getRandomValues(cryptoArray);
        return cryptoArray[0] % max;
    }

    function Cell(x, y) {
        this.x = x;
        this.y = y;
        this.letter = null;

        this.$ = $('<div></div>');
        this.$.addClass('cmsws-cell');
        this.$.data('x', this.x);
        this.$.data('y', this.y);
    }

    Cell.prototype.willFitLetter = function (letter) {
        return this.letter === null || this.letter === letter;
    };

    Cell.prototype.randomFill = function (allowed_chars) {
        if (this.letter == null) {
            const i = getRandomIndex(allowed_chars.length);
            return this.letter = allowed_chars[i];
        }
    };

    Cell.prototype.toHtml = function () {
        this.$.text(this.letter || "");
        return this.$;
    }

    function LetterGrid(wordList = [], allowed_chars = '', directions = [], size = 10, maxWords = 10) {
        this.width = size;
        this.height = size;

        this.directions = shuffleArray(directions);
        allowed_chars = shuffleArray(allowed_chars);

        // int cells
        this.cells = Array.from({ length: this.height }, (_, y) => {
            return Array.from({ length: this.width }, (_, x) => {
                return new Cell(x, y);
            });
        });

        this.used_words = [];
        wordList = shuffleArray(wordList);

        // place the words
        for (const originalWord of wordList) {
            const word = originalWord.replace(/[\s-']/g, '');

            for (let count = 0; count < 10; count++) {
                // try several times to place a word
                if (!this.used_words.includes(word) && this._tryPlaceWord(word)) {
                    this.used_words.push(word);
                    break;
                }
            }

            if (this.used_words.length >= maxWords) {
                break;
            }
        }

        // fill the rest of the cells randomly
        this.cells.forEach(row => row.forEach(cell => cell.randomFill(allowed_chars)));
        this.used_words.sort((a, b) => b.length - a.length);
    };

    LetterGrid.prototype._tryPlaceWord = function (word) {
        const dir = this.directions[getRandomIndex((this.directions.length))];

        if ((dir.x !== 0 && word.length > this.width) || (dir.y !== 0 && word.length > this.height)) {
            return false;
        }

        const getRandomStart = (dir, length, dimension) => {
            const number = getRandomIndex((dimension - length + 1));
            if (dir === -1) {
                return dimension - number;
            }
            return number;
        };

        const start = {
            x: getRandomStart(dir.x, word.length, this.width),
            y: getRandomStart(dir.y, word.length, this.height),
        };

        const end = {
            x: start.x + dir.x * (word.length - 1),
            y: start.y + dir.y * (word.length - 1),
        };

        const path = this.getPath(start, end);

        if (path === null) {
            return false;
        }

        if (!path.every((cell, i) => cell.willFitLetter(word[i]))) {
            return false;
        }

        path.every((cell, i) => cell.letter = word[i]);

        return true;
    };

    LetterGrid.prototype.getPath = function (start, end) {
        const cellPath = [];
        const diff = { x: end.x - start.x, y: end.y - start.y };

        const stepX = (diff.x === 0) ? 0 : diff.x / Math.abs(diff.x);
        const stepY = (diff.y === 0) ? 0 : diff.y / Math.abs(diff.y);

        const current = { x: start.x, y: start.y };

        while (current.x >= 0 && current.y >= 0 &&
            current.x < this.width && current.y < this.height
        ) {
            cellPath.push(this.cells[current.y][current.x]);

            if (current.x === end.x && current.y === end.y) {
                return cellPath;
            }

            current.x += stepX;
            current.y += stepY;
        }

        return null;
    };

    LetterGrid.prototype.isPathAllowed = function (start, end) {
        const diffX = Math.abs(end.x - start.x);
        const diffY = Math.abs(end.y - start.y);

        // Check if the path is diagonal, horizontal, or vertical
        return diffX === diffY || diffX === 0 || diffY === 0;
    };

    LetterGrid.prototype.isPathAWord = function (path, colorId) {
        const selectedWord = path.map(cell => cell.letter).join('');

        if (this.used_words.includes(selectedWord)) {
            path.forEach(cell => {
                cell.$.addClass('cmsws-is-word');
                cell.$.addClass('cmsws-color' + colorId);
            });

            $('#cmsws-' + selectedWord).addClass('found');
            return true;
        }

        return false;
    };

    LetterGrid.prototype.getHtmlTable = function() {
        const tableHtml = $('<div class="cmsws-table"></div>');
        this.cells.forEach(letterRow => {
            const rowHtml = $('<div class="cmsws-row"></div>');
            letterRow.forEach(cell => rowHtml.append(cell.toHtml()));
            tableHtml.append(rowHtml);
        });
        return tableHtml;
    };

    LetterGrid.prototype.getUsedWordsList = function() {
        const row = $('<div id="usedWords" class="used-word-list"></div>');
        this.used_words.forEach(word => {
            const wordBox = $('<div></div>');
            wordBox.addClass("word-box");
            wordBox.text(word);
            wordBox.attr('id', 'cmsws-' + word)
            row.append(wordBox);
        });
        return row;
    };

    function Wordsearch(id) {
        this.$ws_container = $('#cmsws_container_' + id);
        this.allowed_chars = $('#cmsws_allowed_chars_' + id).val().trim();
        this.field_size = $('#cmsws_field_size_' + id).val().trim();
        this.letterGrid = null;
        this.firstClickedCell = null;
        this.found_words = 0;

        this.words = $('#cmsws_custom_words_' + id).val().split(cmsws_front_args.word_separator);
        this.words = this.words.filter(element => element !== "" && element.length <= this.field_size);

        const directionNames = $('#cmsws_directions_' + id).val().split(cmsws_front_args.word_separator);
        this.directions = directionNames.map(direction => {
            const directionMap = {
                'east': { x: 1, y: 0 },
                'south': { x: 0, y: 1 },
                'west': { x: -1, y: 0 },
                'north': { x: 0, y: -1 },
                'southeast': { x: 1, y: 1 },
                'northeast': { x: 1, y: -1 },
                'southwest': { x: -1, y: 1 },
                'northwest': { x: -1, y: -1 }
            };

            return directionMap[direction];
        });

        this.$ws_container.find('#btn-new-game').on('click',function () {
            this.$ws_container.find('#cmws-modal').css('display', 'none');
            this.newGame();
        }.bind(this));

        this.$ws_container.find('#cmsws-closemodale').on('click', function () {
            this.$ws_container.find('#cmws-modal').css('display', 'none');
        }.bind(this));
    };

    Wordsearch.prototype.reSize = function () {
        const maxCellSize = 50;  // Maximum cell size in pixels
        const containerWidth = this.$ws_container.width() * 0.95;
        const cellWidth = Math.min(containerWidth / this.field_size, maxCellSize);
        this.$ws_container.find('.cmsws-cell').css({
            'width': cellWidth + 'px',
            'height': cellWidth + 'px'
        });

    };

    Wordsearch.prototype.newGame = function () {
        this.letterGrid = null;
        this.firstClickedCell = null;
        this.found_words = 0;

        // Remove the old container if it exists
        this.$ws_container.find('#letterGridContainer').remove();
        this.$ws_container.find('#UsedWordsContainer').remove();

        this.letterGrid = new LetterGrid(this.words, this.allowed_chars, this.directions, this.field_size);

        const letterGridContainer = $('<div id="letterGridContainer"></div>');
        const usedWordsContainer = $('<div id="UsedWordsContainer"></div');

        letterGridContainer.append(this.letterGrid.getHtmlTable());
        usedWordsContainer.append(this.letterGrid.getUsedWordsList());

        this.$ws_container.append(letterGridContainer, usedWordsContainer);

        this.reSize();

        letterGridContainer.on('click', '.cmsws-cell', this._onClickCell.bind(this));
        letterGridContainer.on('mouseenter', '.cmsws-cell', this._togglePath.bind(this));
        letterGridContainer.on('mouseleave', '.cmsws-cell', this._togglePath.bind(this));
    };

    Wordsearch.prototype._fetchCell = function (target) {
        const $cell = $(target);
        return this.letterGrid.cells[$cell.data('y')][$cell.data('x')];
    };

    Wordsearch.prototype._onClickCell = function(e) {
        const cell = this._fetchCell(e.currentTarget);
        if (!this.firstClickedCell) {
            // If no cell has been clicked previously, store the current cell
            this.firstClickedCell = cell;
            cell.$.addClass('cmsws-clicked');
        } else if (this.firstClickedCell === cell) {
            // Clicked the same cell again
            this.firstClickedCell = null;
            cell.$.removeClass('cmsws-clicked');
        } else {
            if (this.letterGrid.isPathAllowed(this.firstClickedCell, cell)) {
                const path = this.letterGrid.getPath(this.firstClickedCell, cell);

                if (this.letterGrid.isPathAWord(path, (this.found_words % 4) + 1)) {
                    this.found_words++;
                }

                if (this.found_words >= this.letterGrid.used_words.length) {
                    this.$ws_container.find('#cmws-modal').css('display', 'block');
                }

                path.forEach(cell => cell.$.removeClass('cmsws-on-path'));

                this.firstClickedCell.$.removeClass('cmsws-clicked');
                this.firstClickedCell = null;
            };
        }
    };

    Wordsearch.prototype._togglePath = function (e) {
        if (this.firstClickedCell === null) {
            return;
        }
        const cell = this._fetchCell(e.currentTarget);
        if (cell === this.firstClickedCell) {
            return;
        }

        if (this.letterGrid.isPathAllowed(this.firstClickedCell, cell)) {
            const path = this.letterGrid.getPath(this.firstClickedCell, cell);

            path.forEach(cell => cell.$.toggleClass('cmsws-on-path'));
        };
    };

    $(document).ready(function () {
        let games = [];
        // Iterate over all elements that match the ID pattern
        $('.cmsws_container').each(function (index, element) {
            const post_id = element.id.replace('cmsws_container_', '');

            const wordsearchInstance = new Wordsearch(post_id);
            wordsearchInstance.newGame();
        });

        let resizeTimeout;
        $(window).on('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                games.forEach(game => game.reSize());
            }, 150);
        });


        //disable search on page (Ctrl+F)
        window.addEventListener("keydown", function (e) {
            if (e.keyCode === 114 || (e.ctrlKey && e.keyCode === 70)) {
                e.preventDefault();
            }
        })
    });

})(jQuery);

