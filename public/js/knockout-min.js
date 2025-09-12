/*
 * Knockout.js 簡易版
 * 基本的なobservable、observableArray、computed機能を提供
 */
(function(window) {
    'use strict';
    
    var ko = {};
    
    // observable
    ko.observable = function(initialValue) {
        var _value = initialValue;
        var _subscribers = [];
        
        var observable = function() {
            if (arguments.length === 0) {
                return _value;
            } else {
                _value = arguments[0];
                _subscribers.forEach(function(callback) {
                    callback(_value);
                });
                return this;
            }
        };
        
        observable.subscribe = function(callback) {
            _subscribers.push(callback);
        };
        
        return observable;
    };
    
    // observableArray
    ko.observableArray = function(initialValues) {
        var _array = initialValues || [];
        var _subscribers = [];
        
        var observableArray = function() {
            if (arguments.length === 0) {
                return _array;
            } else {
                _array = arguments[0] || [];
                _subscribers.forEach(function(callback) {
                    callback(_array);
                });
                return this;
            }
        };
        
        // 配列のメソッドを追加
        observableArray.push = function(item) {
            _array.push(item);
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
        };
        
        observableArray.pop = function() {
            var result = _array.pop();
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
            return result;
        };
        
        observableArray.shift = function() {
            var result = _array.shift();
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
            return result;
        };
        
        observableArray.unshift = function(item) {
            _array.unshift(item);
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
        };
        
        observableArray.splice = function() {
            var result = _array.splice.apply(_array, arguments);
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
            return result;
        };
        
        observableArray.remove = function(item) {
            var index = _array.indexOf(item);
            if (index > -1) {
                _array.splice(index, 1);
                _subscribers.forEach(function(callback) {
                    callback(_array);
                });
                return true;
            }
            return false;
        };
        
        observableArray.removeAll = function() {
            _array.length = 0;
            _subscribers.forEach(function(callback) {
                callback(_array);
            });
        };
        
        observableArray.indexOf = function(item) {
            return _array.indexOf(item);
        };
        
        observableArray.subscribe = function(callback) {
            _subscribers.push(callback);
        };
        
        return observableArray;
    };
    
    // computed
    ko.computed = function(evaluator) {
        var _value;
        var _hasBeenEvaluated = false;
        
        var computed = function() {
            if (!_hasBeenEvaluated) {
                _value = evaluator();
                _hasBeenEvaluated = true;
            }
            return _value;
        };
        
        return computed;
    };
    
    // applyBindings
    ko.applyBindings = function(viewModel, rootNode) {
        rootNode = rootNode || document;
        
        // 既にバインディングが適用されているかチェック
        if (rootNode._koApplied) {
            console.warn('Knockout bindings already applied to this element');
            return;
        }
        
        // バインディング適用済みフラグを設定
        rootNode._koApplied = true;
        
        // $rootプロパティを設定（まだ設定されていない場合のみ）
        if (!viewModel.$root) {
            viewModel.$root = viewModel;
        }
        
        // data-bind属性を持つ要素を探す
        var elements = rootNode.querySelectorAll('[data-bind]');
        
        elements.forEach(function(element) {
            var bindingString = element.getAttribute('data-bind');
            var bindings = parseBindings(bindingString);
            
            Object.keys(bindings).forEach(function(bindingName) {
                var bindingValue = bindings[bindingName];
                applyBinding(element, bindingName, bindingValue, viewModel);
            });
        });
        
        // foreach バインディングの処理
        processForEach(rootNode, viewModel);
    };
    
    function parseBindings(bindingString) {
        var bindings = {};
        var parts = bindingString.split(',');
        
        parts.forEach(function(part) {
            var colonIndex = part.indexOf(':');
            if (colonIndex > -1) {
                var key = part.substring(0, colonIndex).trim();
                var value = part.substring(colonIndex + 1).trim();
                bindings[key] = value;
            }
        });
        
        return bindings;
    }
    
    function applyBinding(element, bindingName, bindingValue, viewModel) {
        var value = getValueFromViewModel(bindingValue, viewModel);
        
        switch (bindingName) {
            case 'text':
                if (typeof value === 'function') {
                    element.textContent = value();
                    if (value.subscribe) {
                        value.subscribe(function(newValue) {
                            element.textContent = newValue;
                        });
                    }
                } else {
                    element.textContent = value;
                }
                break;
            case 'visible':
                if (typeof value === 'function') {
                    element.style.display = value() ? 'block' : 'none';
                    if (value.subscribe) {
                        value.subscribe(function(newValue) {
                            element.style.display = newValue ? 'block' : 'none';
                        });
                    }
                } else {
                    element.style.display = value ? 'block' : 'none';
                }
                break;
            case 'value':
                if (typeof value === 'function') {
                    // 初期値を設定
                    element.value = value() || '';
                    
                    // 変更イベントを監視
                    element.addEventListener('input', function() {
                        value(element.value);
                    });
                    
                    // observableの変更を監視
                    if (value.subscribe) {
                        value.subscribe(function(newValue) {
                            element.value = newValue || '';
                        });
                    }
                } else {
                    element.value = value || '';
                }
                break;
            case 'click':
                element.addEventListener('click', function(event) {
                    var func = getValueFromViewModel(bindingValue, viewModel);
                    if (typeof func === 'function') {
                        func.call(viewModel, event);
                    }
                });
                break;
            case 'css':
                // 簡易CSS バインディング
                if (typeof value === 'object' && value !== null) {
                    Object.keys(value).forEach(function(className) {
                        var condition = value[className];
                        if (typeof condition === 'function') {
                            if (condition()) {
                                element.classList.add(className);
                            } else {
                                element.classList.remove(className);
                            }
                        } else {
                            if (condition) {
                                element.classList.add(className);
                            } else {
                                element.classList.remove(className);
                            }
                        }
                    });
                }
                break;
        }
    }
    
    function processForEach(rootNode, viewModel) {
        // ko foreachコメントを探す
        var walker = document.createTreeWalker(
            rootNode,
            NodeFilter.SHOW_COMMENT,
            null,
            false
        );
        
        var comments = [];
        var node;
        while (node = walker.nextNode()) {
            if (node.nodeValue.trim().indexOf('ko foreach:') === 0) {
                comments.push(node);
            }
        }
        
        comments.forEach(function(comment) {
            var foreachMatch = comment.nodeValue.match(/ko foreach:\s*(\w+)/);
            if (foreachMatch) {
                var arrayName = foreachMatch[1];
                var array = getValueFromViewModel(arrayName, viewModel);
                
                if (typeof array === 'function') {
                    renderForeachItems(comment, array(), viewModel);
                    
                    if (array.subscribe) {
                        array.subscribe(function(newArray) {
                            renderForeachItems(comment, newArray, viewModel);
                        });
                    }
                } else if (Array.isArray(array)) {
                    renderForeachItems(comment, array, viewModel);
                }
            }
        });
    }
    
    function renderForeachItems(comment, items, viewModel) {
        if (!Array.isArray(items)) {
            return;
        }
        
        // コメントの次の要素から終了コメントまでを取得
        var startComment = comment;
        var endComment = findEndComment(startComment);
        
        if (!endComment) {
            return;
        }
        
        // 既存の要素を削除
        var current = startComment.nextSibling;
        while (current && current !== endComment) {
            var next = current.nextSibling;
            if (current.nodeType === Node.ELEMENT_NODE) {
                current.remove();
            }
            current = next;
        }
        
        // 各アイテムに対して要素を作成
        items.forEach(function(item, index) {
            // テンプレート要素を探す（コメントの次の要素）
            var template = startComment.nextSibling;
            if (template && template.nodeType === Node.ELEMENT_NODE) {
                // テンプレートをクローン
                var newElement = template.cloneNode(true);
                
                // 新しいViewModelを作成
                var itemViewModel = Object.create(viewModel);
                
                // アイテムのプロパティを直接ViewModelに追加
                Object.keys(item).forEach(function(key) {
                    itemViewModel[key] = item[key];
                });
                
                // 特別なプロパティを追加
                itemViewModel.$data = item;
                itemViewModel.$index = index;
                itemViewModel.$parent = viewModel;
                itemViewModel.$root = getRootViewModel(viewModel);
                
                // 新しい要素にバインディングを適用（再帰的にapplyBindingsを呼ばない）
                var itemElements = newElement.querySelectorAll('[data-bind]');
                itemElements.forEach(function(element) {
                    // 既にバインディングが適用されているかチェック
                    if (element._koApplied) {
                        return;
                    }
                    
                    var bindingString = element.getAttribute('data-bind');
                    var bindings = parseBindings(bindingString);
                    
                    Object.keys(bindings).forEach(function(bindingName) {
                        var bindingValue = bindings[bindingName];
                        applyBinding(element, bindingName, bindingValue, itemViewModel);
                    });
                    
                    // バインディング適用済みフラグを設定
                    element._koApplied = true;
                });
                
                // 要素を挿入
                endComment.parentNode.insertBefore(newElement, endComment);
            }
        });
    }
    
    function findEndComment(startComment) {
        var current = startComment.nextSibling;
        while (current) {
            if (current.nodeType === Node.COMMENT_NODE && 
                current.nodeValue.trim() === '/ko') {
                return current;
            }
            current = current.nextSibling;
        }
        return null;
    }
    
    function getValueFromViewModel(expression, viewModel) {
        try {
            // 簡易的な式の評価
            if (typeof expression === 'string') {
                // 関数定義の場合はそのまま実行
                if (expression.indexOf('function') === 0) {
                    var func = new Function('$data', '$root', 'return ' + expression);
                    var rootViewModel = viewModel.$root || getRootViewModel(viewModel);
                    return func(viewModel, rootViewModel);
                }
                
                // $root参照を処理
                if (expression.indexOf('$root') !== -1) {
                    var rootViewModel = viewModel.$root || getRootViewModel(viewModel);
                    var func = new Function('$data', '$root', 'with($data) { return ' + expression + '; }');
                    return func(viewModel, rootViewModel);
                }
                
                // $parent参照を処理
                if (expression.indexOf('$parent') !== -1) {
                    var func = new Function('$data', '$parent', 'with($data) { return ' + expression + '; }');
                    return func(viewModel, viewModel.$parent || viewModel);
                }
                
                // 通常の式の場合はViewModelのコンテキストで評価
                var func = new Function('$data', 'with($data) { return ' + expression + '; }');
                return func(viewModel);
            }
            return expression;
        } catch (e) {
            console.warn('Failed to evaluate expression:', expression, e);
            return null;
        }
    }
    
    function getRootViewModel(viewModel) {
        // $parentを辿ってルートViewModelを見つける
        var current = viewModel;
        while (current.$parent) {
            current = current.$parent;
        }
        return current;
    }
    
    // グローバルに公開
    window.ko = ko;
    
})(window);