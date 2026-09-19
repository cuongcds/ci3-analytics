<?php
/**
 * Filter form (presets + custom range) and the daily page-views chart.
 * Meant to be embedded into the host app's own dashboard/analytics page —
 * see README "Embedding into your own dashboard".
 * Expects: $report, $formAction, and a Open\Analytics\Support\DateRange
 * instance as $dateRange (for the preset start dates / min/max bounds).
 */
$today = $dateRange->today();
$earliestStart = $dateRange->earliestStart();
$presetStarts = $dateRange->presetStarts();
?>
<div style="margin-bottom:16px;display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:12px;">
	<form method="get" action="<?= htmlspecialchars($formAction) ?>" style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
		<div style="display:flex;border:1px solid #d1d5db;border-radius:6px;overflow:hidden;font-size:.875rem;">
			<?php foreach ($presetStarts as $days => $presetStart): $isActive = $report['start_date'] === $presetStart && $report['end_date'] === $today; ?>
			<button type="submit" name="preset" value="<?= (int) $days ?>"
				style="padding:6px 12px;border:0;border-right:1px solid #d1d5db;cursor:pointer;<?= $isActive ? 'background:#6366f1;color:#fff;' : 'background:#fff;' ?>">
				<?= (int) $days ?> days
			</button>
			<?php endforeach; ?>
		</div>
		<input type="date" name="start" value="<?= htmlspecialchars($report['start_date']) ?>" min="<?= $earliestStart ?>" max="<?= $today ?>"
			style="border:1px solid #d1d5db;border-radius:6px;padding:6px 8px;font-size:.875rem;">
		<span style="font-size:.875rem;color:#6b7280;">&ndash;</span>
		<input type="date" name="end" value="<?= htmlspecialchars($report['end_date']) ?>" min="<?= $earliestStart ?>" max="<?= $today ?>"
			style="border:1px solid #d1d5db;border-radius:6px;padding:6px 8px;font-size:.875rem;">
		<button type="submit" style="border:0;border-radius:6px;padding:6px 12px;font-size:.875rem;font-weight:500;background:#6366f1;color:#fff;cursor:pointer;">
			Apply
		</button>
	</form>
</div>

<div style="border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
	<p style="margin:0 0 12px;font-size:.875rem;font-weight:600;color:#6b7280;text-transform:uppercase;">Page views over time</p>
	<canvas id="analyticsDailyChart" height="80"></canvas>
</div>

<script>
	window.OPEN_ANALYTICS_DAILY_SERIES = <?= json_encode(array_map(function ($row) {
		return ['day' => $row['day'], 'total' => (int) $row['total']];
	}, $report['daily_page_views'])) ?>;
</script>
