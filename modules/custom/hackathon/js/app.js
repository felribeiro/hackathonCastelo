(function() {
    $(document).ready(function() {
        $('select').material_select();
    });
    
    var dateConfig = window.datepickerPT_BR;
    dateConfig.selectMonths = true;
    dateConfig.selectYears = 10;
    dateConfig.formatSubmit = 'yyyy-mm-dd',
    
    $('.datepicker').pickadate(dateConfig);
    
    var solutionData = {
        payment_method: null
        , payment_date: null
    };
    
    var noSolutionData = {
        solution_justify: null
        , solution_justify_detail: null
        , user_offer: null
    };
    
    var anySolutionFinishStep = function(collection) {
        requiredFields = Object.keys(collection);
        
        var valid = true;
        requiredFields.forEach(function(field) {
            if (!collection[field]) {
                valid = false;
            }
        });
        
        if (valid) {
            $('#finish').show();
            scrollTo('#finish');
        }
    };
    
    $('.btn-solution').click(function(e) {
        e.preventDefault();
        
        $('#solution_type').val(1);
        $('#solution-step-1').show();
        preventDoubleForm('no-solution-step');
        scrollTo('#solution-step-1');
    });
    
    $('[name="payment_method"]').change(function() {
        var value = $(this).val();
        if (value) {
            solutionData[this.name] = value;
            anySolutionFinishStep(solutionData);
        }
    });
    
    $('[name="payment_date"]').change(function() {
        var value = $(this).val();
        if (value) {
            solutionData[this.name] = value;
            anySolutionFinishStep(solutionData);
        }
    });
    
    $('.btn-no-solution').click(function(e) {
        e.preventDefault();

        $('#solution_type').val(2);
        $('#no-solution-step-1').show();
        preventDoubleForm('solution-step');
        scrollTo('#no-solution-step-1');
    });
    
    $('#no-solution-step-1 select').change(function() {
        var value = $(this).val();
        if (value) {
            noSolutionData[this.name] = value;
            switch(parseInt(value)) {
                case 1:
                    delete noSolutionData.solution_justify_detail;
                    $('#no-solution-step-3').show();
                    scrollTo('#no-solution-step-3');
                    break;
                
                case 2:
                    if (!noSolutionData.hasOwnProperty('solution_justify_detail')) {
                        noSolutionData.solution_justify_detail = null;
                    }
                    $('#no-solution-step-2').show();
                    scrollTo('#no-solution-step-2');
                    break;
            }
        }
    });
    
    $('#no-solution-step-2 select').change(function() {
        var value = $(this).val();
        if (value) {
            noSolutionData[this.name] = value;
            $('#no-solution-step-3').show();
            scrollTo('#no-solution-step-3');
        }
    });
    
    $('#no-solution-step-3 textarea').change(function() {
        var value = $(this).val();
        if (value) {
            noSolutionData[this.name] = value;
            $('#no-solution-step-info').show();
            anySolutionFinishStep(noSolutionData);
        }
    });
    
    $('#submit-solution').click(function(e) {
        e.preventDefault();
        
        var data = $('form').serializeArray();
        
        $.ajax({
            method: 'POST'
            , url: '/solution'
            , data: data
        }).then(function(res) {
            if (res.success === true) {
                $('#soluton-content').hide();
                $('#complete').show();
            }            
        });
//        console.log($('form').serializeArray());
    });
    
    function scrollTo(hash) {
        $('html, body').animate({
            scrollTop: $(hash).offset().top
        }, 1500);
    }
    
    function preventDoubleForm(fieldSetName) {
        $('[id^="' + fieldSetName + '"]').each(function(ix, item) {
            $(item).hide();
        });
        $('#finish').hide();
    }
    
}());

