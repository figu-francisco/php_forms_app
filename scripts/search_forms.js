
$(function(){

    //remove form when JS enabled, use services to filter forms instead
    $("#search_form").remove();

    //if there is an ecoded search in memory
    //decode and put it as input
    encoded_search = $("#encoded_search_input").val();

    //when arriving to the page with a preencoded search 
    if(encoded_search != ""){
        //service for decoding search
        $.get("form/decode_search/" + encoded_search,
        function(data){
            let decoded_data = JSON.parse(data);
            /* exemple returned data (text + color)
                {
                    "texte": "sh",
                    "color": ["blue","red","yellow","green"]
                }
            */
           //update visual accoridng to search
            update_field_and_checkbox(decoded_data);
            filter_cards_encode_search($("#input_search").val());
        }); 
    }

    base_url_openbtn = $("#base_url_openbtn").val();
    let input_search = $("#input_search");

    //forms search input listener
    input_search.on("input", function() {
        filter_cards_encode_search(input_search.val());
    });

    //color category listener
    let input_color_selector = $("#search_color_div input[type='checkbox']");
    input_color_selector.change(function() {
        filter_cards_encode_search(input_search.val());
    });
});

async function filter_cards_encode_search(input_search){
    let color_search = get_checked_box(); //null if no checkbox checked
    
    //hides all cards
    $("#cards_container").children().hide();
    
    //service to get filtered cards ids
    try{   
        await $.post("form/get_ids_filtered_ajax/",
            {"filter": input_search, choice: color_search },
        function(data){
            let data_array = JSON.parse(data);
            let encoded_search = data_array.pop();//remove last element (encoded_search)

            //show ids in json data_array
            $.each(data_array, function(index, value) {
                let base_url_managebtn = $(`#base_url_managebtn_${value}`).val();
                let selector = `#${value}`;
                $(selector).show();
                let openbtn = `#openbtn_${value}`;
                let managebtn = `#managebtn_${value}`;
                const new_url_openbtn = base_url_openbtn + encoded_search;
                const new_url_managebtn = base_url_managebtn + encoded_search;
                //propagates encoded search
                $(openbtn).attr("action",
                    input_search == "" && color_search.length === 0 ? base_url_openbtn : new_url_openbtn);
                $(managebtn).attr("href",
                    input_search == "" && color_search.length === 0 ? base_url_managebtn : new_url_managebtn);
            }); 
        });
    }catch(e){
        console.error("AJAX error: ", e.responseText);
        alert(e.responseText);
    }
}

function get_checked_box(){
    let selected_colors = [];
    $("#search_color_div input[type='checkbox']:checked").each(function () {
        selected_colors.push($(this).val());
    });
    return selected_colors;
}

function update_field_and_checkbox(decoded_data){
    $("#input_search").val(decoded_data.texte);
    for (let color_name of decoded_data.color) {
        let checkbox = $("#search_color_div input[type='checkbox'][value='" + color_name + "']");
        checkbox.prop("checked", true);
    }
}