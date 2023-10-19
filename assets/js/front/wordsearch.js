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
            i = Math.floor(Math.random() * allowed_chars.length);
            return this.letter = allowed_chars[i];
        }
    };

    Cell.prototype.toHtml = function () {
        this.$.text(this.letter || "");
        return this.$;
    }

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

        const start = {
            x: getRandomStart(dirX, word.length, this.width),
            y: getRandomStart(dirY, word.length, this.height),
        };

        const end = {
            x: start.x + dirX * (word.length - 1),
            y: start.y + dirY * (word.length - 1),
        };

        const path = this.getPath(start, end);

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

    LetterGrid.prototype.getPath = function (start, end) {
        const cellPath = [];

        const startX = start.x;
        const startY = start.y;
        const endX = end.x;
        const endY = end.y;

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

    LetterGrid.prototype.isPathAllowed = function (start, end) {
        const diffX = Math.abs(end.x - start.x);
        const diffY = Math.abs(end.y - start.y);

        // Check if the path is diagonal, horizontal, or vertical
        if (diffX === diffY || diffX === 0 || diffY === 0) {
            return true; // Path is allowed
        } else {
            return false; // Path is not allowed
        }
    };

    LetterGrid.prototype.isPathAWord = function (path, colorId) {
        let selectedWord = '';
        for (const cell of path) {
            if (cell.letter !== null) {
                selectedWord += cell.letter;
            }
        }
        if (this.used_words.includes(selectedWord)) {
            path.forEach(cell => {
                cell.$.addClass('cmsws-is-word');
                cell.$.addClass('cmsws-color' + colorId);
            });
            const word = $('#cmsws-'+selectedWord);
            word.addClass('found');
            return true;
        }
        return false;
    };

    LetterGrid.prototype.getHtmlTable = function() {
        const table = $('<div class="cmsws-table"></div>');
        this.cells.forEach(letterRow => {
            const row = $('<div class="cmsws-row"></div>');

            letterRow.forEach(cell => {
                row.append(cell.toHtml());
            });

            table.append(row);
        });
        return table;
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

    let cms_wordsearch = {
        $ws_container: $('#cms_wordsearch_container'),
        words: $('#cmsws_custom_words').val().split(cmsws_front_args.word_seperator),
        allowed_chars: $('#cmsws_allowed_chars').val().trim(),
        field_size: $('#cmsws_field_size').val().trim(),
        word_list_position: $('#cmsws_field_word_list_position').val().trim(),
        directions: Array(),
        letterGrid: null,
        firstClickedCell: null,
        found_words: 0,

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
        },

        newGame: function () {
            this.directions = shuffleArray(this.directions);
            this.allowed_chars = shuffleArray(this.allowed_chars);

            this.letterGrid = null;
            this.firstClickedCell = null;
            this.found_words = 0;
            // Remove the old container if it exists
            $('#letterGridContainer').remove();
            $('#UsedWordsContainer').remove();

            this.letterGrid = new LetterGrid(this.words, this.allowed_chars, this.directions, this.field_size);

            let newContainer = $('<div id="letterGridContainer"></div>');
            const table = this.letterGrid.getHtmlTable();
            newContainer.append(table);
            this.$ws_container.append(newContainer);

            newContainer = $('<div id="UsedWordsContainer"></div>');
            const list = this.letterGrid.getUsedWordsList();
            newContainer.append(list);
            this.$ws_container.append(newContainer);

            table.on('click', '.cmsws-cell', cms_wordsearch._onClickCell.bind());
            table.on('mouseenter', '.cmsws-cell', cms_wordsearch._togglePath.bind());
            table.on('mouseleave', '.cmsws-cell', cms_wordsearch._togglePath.bind());
        },

        _fetchCell: function (target) {
            const $cell = $(target);
            let x = $cell.data('x');
            let y = $cell.data('y');
            return cms_wordsearch.letterGrid.cells[y][x];
        },

        _onClickCell: function(e) {
            const cell = cms_wordsearch._fetchCell(e.currentTarget);
            if (cms_wordsearch.firstClickedCell === null) {
                // If no cell has been clicked previously, store the current cell
                cms_wordsearch.firstClickedCell = cell;
                cell.$.addClass('cmsws-clicked');
            } else if (cms_wordsearch.firstClickedCell === cell) {
                // Clicked the same cell again
                cms_wordsearch.firstClickedCell = null;
                cell.$.removeClass('cmsws-clicked');
            } else {
                if (cms_wordsearch.letterGrid.isPathAllowed(cms_wordsearch.firstClickedCell, cell)) {
                    const path = cms_wordsearch.letterGrid.getPath(cms_wordsearch.firstClickedCell, cell);

                    if (cms_wordsearch.letterGrid.isPathAWord(path, (cms_wordsearch.found_words % 4) + 1)) {
                        cms_wordsearch.found_words++;
                    }

                    if (cms_wordsearch.found_words >= cms_wordsearch.letterGrid.used_words.length) {
                        $('#cmws-modal').css('display', 'block');
                    }

                    path.forEach(cell => {
                        cell.$.removeClass('cmsws-on-path');
                    });

                    cms_wordsearch.firstClickedCell.$.removeClass('cmsws-clicked');
                    cms_wordsearch.firstClickedCell = null;
                };
            }
        },

        _togglePath: function (e) {
            if (cms_wordsearch.firstClickedCell === null) {
                return;
            }
            const cell = cms_wordsearch._fetchCell(e.currentTarget);
            if (cell === cms_wordsearch.firstClickedCell) {
                return;
            }

            if (cms_wordsearch.letterGrid.isPathAllowed(cms_wordsearch.firstClickedCell, cell)) {
                const path = cms_wordsearch.letterGrid.getPath(cms_wordsearch.firstClickedCell, cell);

                path.forEach(cell => {
                    cell.$.toggleClass('cmsws-on-path');
                })
            };
        },
    };

    $(document).ready(function () {
        cms_wordsearch.init();
        cms_wordsearch.newGame();

        $('#btn-new-game').on('click', function () {
            $('#cmws-modal').css('display', 'none');
            cms_wordsearch.newGame();
        })

        //disable search on page (Ctrl+F)
        window.addEventListener("keydown", function (e) {
            if (e.keyCode === 114 || (e.ctrlKey && e.keyCode === 70)) {
                e.preventDefault();
            }
        })
    });
})(jQuery);

