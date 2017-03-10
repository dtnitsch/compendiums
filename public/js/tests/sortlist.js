'use strict';

/**
 * Performance handler
 * The point of this is simply to get how long the test took to run, its not to
 * test if the function works.  Im using Qunits time tracker in a hacky way
 *
 * @param  {function} func     function to test
 * @param  {array} args      array of arguments
 * @param  {number} iterations Number of times to execute the function
 * @return {true}            returns true
 */

var myPerf = function(iterations){
    return function(func, args){
        var i
        for(i = 0; i < iterations; i++){
            func.call(args);
        }
        return true;
    }
}

// this is my alteration so I can run both MIN / FULL tests
var runTests = function(title, sortlist){

    QUnit.test(title + 'Remote Defaults', function(assert){
        var props;
        var asl_sort = sortlist().remote;
        asl_sort.init('/ajax.php');

        assert.equal(asl_sort.id, 'sortlist_ajax', 'Setting default id');
        assert.equal(asl_sort.rpp, 20, 'Default of 20 rows per page');
        assert.equal(asl_sort.url, '/ajax.php', 'Ajax File');
        assert.equal(asl_sort.cache, true, 'Cache: true');
        assert.equal(asl_sort.p, false, 'Pagination: false');
        assert.equal(asl_sort.filters, '', 'No default filters');
    });

    QUnit.test(title + 'remote.init', function(assert){
        var props;
        var asl_sort = sortlist().remote;
        asl_sort.init('/ajax.php',{
            id:'sortlist_ajax'
            ,data: 'apid=02a54624a9ea0058b4ab1b96265afd84'
            ,type: "pagination"
        });

        assert.equal(asl_sort.rpp, 20, 'Default of 20 rows per page');
        assert.equal(asl_sort.id, 'sortlist_ajax', 'Setting default id');
    });

    QUnit.test(title + 'remote.init', function(assert){
        var props;
        var asl_sort = sortlist().remote;
        asl_sort.init('/ajax.php',{
            id:'sortlist_ajax'
            ,data: 'apid=02a54624a9ea0058b4ab1b96265afd84'
            ,type: "pagination"
        });
        asl_sort.sort();

        assert.equal(asl_sort.rpp, 20, 'Default of 20 rows per page');
        assert.equal(asl_sort.id, 'sortlist_ajax', 'Setting default id');
    });

    // QUnit.test(title + 'trim2', function(assert){
    //     assert.equal(sortlist.trim2(' 123 '), '123', 'Trim both sides');
    //     assert.equal(sortlist.trim2(' 12   3 '), '12   3', 'Trim with space in the middle');
    // });

    // QUnit.test(title + 'trim1 ( non strings )', function(assert){
    //     // how will you deal with non strings? This craps out
    //     assert.equal(sortlist.trim1(123), '123', 'numbers');
    //     assert.equal(sortlist.trim1(null), '', 'null');
    //     assert.equal(sortlist.trim1(true), '', 'true');
    //     assert.equal(sortlist.trim1(false), '', 'false');
    //     assert.equal(sortlist.trim1(undefined), '', 'undefined');
    // });

    // QUnit.test(title + 'trim2 ( non strings )', function(assert){
    //     // how will you deal with non strings? This craps out
    //     assert.equal(sortlist.trim2(123), '123', 'numbers');
    //     assert.equal(sortlist.trim2(null), '', 'null');
    //     assert.equal(sortlist.trim2(true), '', 'true');
    //     assert.equal(sortlist.trim2(false), '', 'false');
    //     assert.equal(sortlist.trim2(undefined), '', 'undefined');
    // });


    // QUnit.test(title + 'PERFORMANCE - 1', function(assert){

    //     // set iteration in one place
    //     var p = myPerf(100000)

    //     // these will always return OK
    //     assert.equal(p(sortlist.trim1, '   trim   '), true);
    //     // assert.equal(p(sortlist.trim2, '   trim   '), true);
    // });

    // QUnit.test(title + 'PERFORMANCE - 2', function(assert){

    //     // set iteration in one place
    //     var p = myPerf(100000)

    //     // these will always return OK
    //     // assert.equal(p(sortlist.trim1, '   trim   '), true);
    //     assert.equal(p(dan.trim2, '   trim   '), true);
    // });

};

runTests('', sortlist);
// runTests(' -- Minified -- ', danmin());