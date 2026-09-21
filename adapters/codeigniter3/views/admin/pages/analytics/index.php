<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Analytics</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.x/dist/chart.umd.min.js"></script>
</head>
<body style="font-family:system-ui,sans-serif;margin:0;padding:24px;background:#f9fafb;color:#111827;">
	<h1 style="font-size:1.25rem;font-weight:600;margin:0 0 16px;">Analytics</h1>

	<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
		<?php $this->load->view('admin/pages/analytics/_summary_cards', ['report' => $report]); ?>
	</div>

	<div style="margin-top:32px;">
		<?php $this->load->view('admin/pages/analytics/_overview', ['report' => $report, 'formAction' => $formAction, 'dateRange' => $dateRange]); ?>
	</div>

	<?php if (!empty($report['event_breakdown'])): ?>
	<div style="margin-top:24px;border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
		<p style="margin:0 0 12px;font-size:.875rem;font-weight:600;color:#6b7280;text-transform:uppercase;">Event breakdown</p>
		<ul style="margin:0;padding:0;list-style:none;font-size:.875rem;">
			<?php foreach ($report['event_breakdown'] as $row): ?>
			<li style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f3f4f6;">
				<span style="color:#6b7280;"><?= htmlspecialchars($row['event_type']) ?></span>
				<span style="font-weight:500;"><?= (int) $row['total'] ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<?php if (!empty($report['top_subjects'])): ?>
	<div style="margin-top:24px;border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
		<p style="margin:0 0 12px;font-size:.875rem;font-weight:600;color:#6b7280;text-transform:uppercase;">Top subjects</p>
		<table style="width:100%;border-collapse:collapse;font-size:.875rem;">
			<thead>
				<tr style="text-align:left;color:#6b7280;border-bottom:1px solid #e5e7eb;">
					<th style="padding:6px 8px 6px 0;">Subject ID</th>
					<th style="padding:6px 8px;text-align:right;">Page views</th>
					<th style="padding:6px 0 6px 8px;text-align:right;">Clicks</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($report['top_subjects'] as $row): ?>
				<tr style="border-bottom:1px solid #f3f4f6;">
					<td style="padding:6px 8px 6px 0;"><?= (int) $row['subject_id'] ?></td>
					<td style="padding:6px 8px;text-align:right;"><?= (int) $row['page_views'] ?></td>
					<td style="padding:6px 0 6px 8px;text-align:right;"><?= (int) $row['clicks'] ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p style="margin-top:8px;font-size:.75rem;color:#9ca3af;">Join subject_id against your own table for display fields — see README "Top subjects".</p>
	</div>
	<?php endif; ?>

	<?php if (!empty($report['top_paths'])): ?>
	<div style="margin-top:24px;border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
		<p style="margin:0 0 12px;font-size:.875rem;font-weight:600;color:#6b7280;text-transform:uppercase;">Most viewed pages</p>
		<table style="width:100%;border-collapse:collapse;font-size:.875rem;">
			<thead>
				<tr style="text-align:left;color:#6b7280;border-bottom:1px solid #e5e7eb;">
					<th style="padding:6px 8px 6px 0;">Page</th>
					<th style="padding:6px 0 6px 8px;text-align:right;">Page views</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($report['top_paths'] as $row): ?>
				<tr style="border-bottom:1px solid #f3f4f6;">
					<td style="padding:6px 8px 6px 0;word-break:break-all;">
						<?php if (!empty($row['domain'])): ?>
						<a href="<?= 'https://' . htmlspecialchars($row['domain']) . htmlspecialchars($row['path']) ?>" target="_blank" rel="noopener" style="color:inherit;">
							<span style="color:#6b7280;"><?= htmlspecialchars($row['domain']) ?></span><?= htmlspecialchars($row['path']) ?>
						</a>
						<?php else: ?>
						<?= htmlspecialchars($row['path']) ?>
						<?php endif; ?>
					</td>
					<td style="padding:6px 0 6px 8px;text-align:right;"><?= (int) $row['page_views'] ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<?php if (!empty($report['top_domains'])): ?>
	<div style="margin-top:24px;border-radius:8px;border:1px solid #e5e7eb;padding:20px;">
		<p style="margin:0 0 12px;font-size:.875rem;font-weight:600;color:#6b7280;text-transform:uppercase;">Most viewed domains</p>
		<table style="width:100%;border-collapse:collapse;font-size:.875rem;">
			<thead>
				<tr style="text-align:left;color:#6b7280;border-bottom:1px solid #e5e7eb;">
					<th style="padding:6px 8px 6px 0;">Domain</th>
					<th style="padding:6px 8px;text-align:right;">Page views</th>
					<th style="padding:6px 0 6px 8px;text-align:right;">Unique visitors</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($report['top_domains'] as $row): ?>
				<tr style="border-bottom:1px solid #f3f4f6;">
					<td style="padding:6px 8px 6px 0;">
						<a href="<?= 'https://' . htmlspecialchars($row['domain']) ?>" target="_blank" rel="noopener" style="color:inherit;"><?= htmlspecialchars($row['domain']) ?></a>
					</td>
					<td style="padding:6px 8px;text-align:right;"><?= (int) $row['page_views'] ?></td>
					<td style="padding:6px 0 6px 8px;text-align:right;"><?= (int) $row['unique_visitors'] ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>

	<!-- From @cuongcds/open-analytics — renders #analyticsDailyChart / #analyticsDauChart from the OPEN_ANALYTICS_*_SERIES globals _overview.php sets. -->
	<script src="https://cdn.jsdelivr.net/npm/@cuongcds/open-analytics@0.1.0/dist/open-analytics-chart.min.js"></script>
</body>
</html>
