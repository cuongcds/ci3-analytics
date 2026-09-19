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
</body>
</html>
