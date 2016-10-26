$(function () {
    const PATTERNS = {
            "username": /[A-Za-z][A-Za-z0-9_]{2,20}/,
            "email": /^\S+?@+?\S+?$/
        },
        ICONS = {
            error: "fa fa-times-circle",
            warning: "fa fa-warning",
            success: "fa fa-check",
            info: "fa fa-info-circle"
        };

    /**
     * Form input object
     * @param elem jQuery object
     * @constructor
     * @param varArgs
     */
    function Input(elem, varArgs) {
        if (varArgs === undefined) varArgs = {};
        this.box = elem;
        this.note = varArgs.note === undefined ? null : varArgs.note;
        this.icon = varArgs.icon === undefined ? null : varArgs.icon;
        this.content = varArgs.content === undefined ? null : varArgs.content;
        if (this.note === null) {
            var possibleNotes = elem.siblings(".note");
            if (possibleNotes.length > 0) {
                this.note = possibleNotes;
            }
        }
        if (this.note !== null && this.icon === null) {
            var possibleIcon = this.note.children("i");
            if (possibleIcon.length > 0) this.icon = possibleIcon;
        }
        if (this.note !== null && this.content === null) {
            var possibleContent = this.note.children(".note-content");
            if (possibleContent.length > 0) this.content = possibleContent;
        }
    }

    /**
     * All keys are optional.
     * If no icon value is passed and the type is defined, the icon will be chosen based on the type.
     * Content can be text or a DOM object.
     * e.g. {type: "warning", content: "This is potentially dangerous", icon: "fa fa-warning", show: false}
     * @param options
     */
    Input.prototype.createNote = function (options) {
        if (this.note === null) {
            this.note = $("<span>").addClass("note").insertAfter(this.box);
        }

        if (options.type !== undefined || options.icon !== undefined) {
            if (this.icon === null) {
                this.icon = $("<i>");
                this.note.prepend(this.icon);
            }
        }

        if (options.content !== undefined && this.content === null) {
            this.content = $("<span>").addClass("note-content");
            this.note.append(this.content);
        }

        if (options.show !== undefined && options.show !== null && options.show === false) {
            this.note.hide();
        } else {
            this.note.show();
        }

        this.setType(options.type, options.icon);
        this.setContent(options.content);
        this.setIcon(options.icon);
        return this;
    };

    Input.prototype.setIcon = function (icon) {
        if (icon === undefined) {
        } else if (icon === null) {
            this.icon.removeClass();
        } else if (this.icon === null) {
            this.createNote({icon: icon, show: false});
        } else {
            this.icon.removeClass().addClass(ICONS[icon] !== undefined ? ICONS[icon] : icon);
        }
        return this;
    };

    /**
     * Sets content of .note-content and shows .note
     * @param content
     */
    Input.prototype.setContent = function (content) {
        if (content === undefined || content === null) {
        } else if (this.content === null) {
            this.createNote({content: content, show: false});
        } else {
            this.content.empty();
            if (content instanceof jQuery) {
                this.content.replaceWith(content.addClass("node-content"));
            } else {
                this.content.text(content);
            }
        }
        return this;
    };

    Input.prototype.setType = function (type, opt_icon) {
        if (type === undefined || type === null || (this.note !== null && this.note.parent().hasClass(type))) {
        } else if (this.note === null) {
            this.createNote({type: type, show: false});
        } else {
            this.box.parent().removeClass("info success warning error").addClass(type);
            if ((opt_icon === undefined || opt_icon === null || opt_icon !== 'none' ) && ICONS[type] !== undefined) {
                this.setIcon(ICONS[type]);
            }
        }
        return this;
    };

    Input.prototype.showNote = function(){
        this.note.slideDown();//addClass("hidden");
    };

    Input.prototype.hideNote = function(){
        this.note.slideUp();//addClass("hidden");
    };

    var arrow = $('.arrow'),
        username_check = null,
        email_check = null,
        passValid=false,
        form = {
            pass1: new Input($('#password1')),
            pass2: new Input($('#password2')),
            email: new Input($('#email')),
            username: new Input($("#username")),
            lazy: new Input($("#lazy"), {note: $("#lazy-row").children(".note")}),
            base: $('form')
        };

    if (form.lazy.box.checked){
        form.pass2.box.attr("disabled", true);
        form.email.box.attr("required", true);
    }

    // Handle form submissions
    form.base.on('submit', function (e) {

        // Is everything entered correctly?
        if ($('form .input-row.error input[type!="submit"]:enabled').length === 0) {

        } else {
            // No. Prevent form submission
            e.preventDefault();
        }
    });

    form.lazy.box.change(function () {
        if (this.checked) {
            form.email.box.attr("disabled", "true").removeAttr("required");
            form.email.box.parent().slideUp();
            form.lazy.showNote();
            $("#lazy-collapse").show();
            form.pass2.box.removeAttr('disabled');
        } else {
            form.email.box.removeAttr("disabled").attr("required", "true");
            form.email.box.parent().slideDown();
            form.lazy.hideNote();
            $("#lazy-collapse").hide();
            if (!passValid){
                form.pass2.box.attr('disabled', 'true');
            }
        }
        if (form.pass1.box.val().length!==0 || form.pass2.box.val().length!==0 ){
            form.pass1.box.keyup();
        }
    });

    //Username availability check
    form.username.box.on('input propertychange paste', function () {
        clearTimeout(username_check);
        username_check = setTimeout(function () {
            if (!isValid(form.username, "username")) {
                var username = form.username.box.val();
                if (username.length > 20) {
                    form.username.setContent("Username too long. (max 20)").showNote();
                } else if (username.length < 3) {
                    form.username.setContent("Username too short. (min 3)").showNote();
                } else if (username.substr(0, 1) === '_') {
                    form.username.setContent("Username cannot start with underscore.").showNote();
                }
            }
        }, 2000); //Don't check for every key stroke
    });

    form.email.box.on('input propertychange paste', function () {
        clearTimeout(email_check);
        email_check = setTimeout(function () {
            if (!isValid(form.email, "email")) {
                form.email.setContent("This email is not valid.").showNote();
            }
        }, 3000); //Don't check for every key stroke
    });

    // Use the complexify plugin on the first password field
    form.pass1.box.complexify({minimumChars: 6, strengthScaleFactor: 0.7}, function (valid, complexity) {
        passValid =valid;
        validate_pass2();

        if (valid || form.lazy.box.prop("checked")) {
            if (form.pass1.box.val() !== form.pass2.box.val()) {
                form.pass2.setType('error');
            }
            if (form.pass1.box.val().length === 0) {
                form.pass1.setType('error');
                form.pass1.setContent("You can't leave this empty!").showNote();
            } else if (!valid && form.lazy.box.prop("checked")) {
                form.pass1.setType('warning');
                form.pass1.setContent("Although it is not enforced, we discourage the use of weak passwords.").showNote();
            } else {
                form.pass1.setType('success').hideNote();
            }
            form.pass2.box.removeAttr('disabled');
        } else {
            form.pass2.box.attr('disabled', 'true');
            form.pass1.setType('error');
            form.pass2.setType('error');
        }

        var calculated = (complexity / 100) * 268 - 134;
        var prop = 'rotate(' + (calculated) + 'deg)';

        // Rotate the arrow
        arrow.css({
            '-moz-transform': prop,
            '-webkit-transform': prop,
            '-o-transform': prop,
            '-ms-transform': prop,
            'transform': prop
        });
    });

    // Validate the second password field
    form.pass2.box.on('input propertychange paste', validate_pass2);

    function validate_pass2(event) {
        // Make sure its value equals the first's
        if (form.pass2.box.val() == form.pass1.box.val() && form.pass1.box.val().length > 0) {
            form.pass2.setType('success').hideNote();
        } else {
            form.pass2.setType('error');
            if (form.pass2.box.val() != form.pass1.box.val()){
                form.pass2.setContent("Passwords do not match!").showNote();
            }else{
                form.pass2.hideNote();
            }
        }
    }

    $(".collapse-icon").click(function () {
        var selector = $(this).toggleClass("collapsed").attr("data-collapse-target");
        $(selector).slideToggle();
    });

    function isValid(needle, type) {
        if (PATTERNS[type].test(needle.box.val())) {
            var data = {};
            data[type] = needle.box.val();
            $.ajax({
                url: type + "_availability.php",
                method: "POST",
                data: data,
                dataType: "json"
            }).done(function (msg) {
                if (msg && msg.type && msg.message) {
                    needle.createNote({type: msg.type, content: msg.message});
                }
            }).always(function (data) {
                if (!(data && data.type && data.message) || (typeof data.getAllResponseHeaders === 'function' && data.getAllResponseHeaders())) {
                    //If ajax request failed because the requested data was not properly returned; fall back on server validation.
                    needle.createNote({
                        type: "warning",
                        content: "Could not check if this " + type + " is unique. The server will retry upon submitting."
                    });
                }
            });
            return true;
        } else {
            needle.setType("error");
            return false;
        }

    }
});
