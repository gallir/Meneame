function disable_league_votes(text) {
	$('.team-votes-menealo').addClass('disabled').text(text);
}

function league_init(vstatus, url) {
	var values = {A: 1, B: 2, C: 0};

	$('.team-votes-menealo').click(function(e) {
		e.preventDefault();
		var self = $(this),
			match  = self.parents('div.game'),
			parent = self.parents('div.team');
			link_id = match.attr('id').match(/match-([0-9]+)/)[1],
			vote = values[parent.attr('class').match(/team.*-([abc])$/i)[1]];

		var objects = {
			local: $('.team-A .team-votes-number', match),
			visitor: $('.team-B .team-votes-number', match),
			tied: $('.team-votes-box-C .team-votes-number', match)
		};

		if (self.hasClass('disabled')) {
			return;
		}

		var content = {id: link_id, user: user_id, key: base_key, u: document.referrer, vote: vote};
		$.getJSON(url + $.param(content), function(data) {
			if (data.error) {
				disable_league_votes('grr..');
				/**  fixme! (mDialog doesn't work) */
				alert("'Error:"+ data.error, 5);
				return;
			}
			objects.local.text(data.local);
			objects.visitor.text(data.visitor);
			objects.tied.text(data.tied);

			get_votes("league_meneos.php", "voters", "voters-container-" + link_id, 1, link_id);

			var target;
			switch (parseInt(data.voted)) {
			case 1:
				target = objects.local;
				break;
			case 2:
				target = objects.visitor;
				break;
			case 0:
				target = objects.tied;
				break;
			}

			if (target) {
				$('.team .team-votes-menealo', match).text(vstatus[1]);
				$('.team-votes-box-C .team-votes-menealo', match).text(vstatus[2]);
				$('.team-votes-button-A > div', match).addClass('team-votes-button-B');
				target
				  .parents('.team')
				  .find('.team-votes-button-A > div').attr('class', '').end()
				  .find('.team-votes-menealo').text(vstatus[0]);
			}
		});
	});
}
