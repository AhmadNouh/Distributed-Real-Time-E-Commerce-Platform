import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notifications_provider.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});
  @override
  Widget build(BuildContext context) {
    final items = context.watch<NotificationsProvider>().notifications;
    return Scaffold(appBar: AppBar(title: const Text('Updates')), body: ListView.separated(
      padding: const EdgeInsets.all(20), itemCount: items.length, separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (_, i) => Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(18)), child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Container(padding: const EdgeInsets.all(11), decoration: const BoxDecoration(color: Color(0xFFDDEBE3), shape: BoxShape.circle), child: const Icon(Icons.notifications_none, color: Color(0xFF285D55))),
        const SizedBox(width: 12), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(items[i].title, style: const TextStyle(fontWeight: FontWeight.w800)), const SizedBox(height: 5),
          Text(items[i].message), const SizedBox(height: 8),
          Text(items[i].time, style: const TextStyle(fontSize: 11, color: Color(0xFF7C8794))),
        ])),
      ])),
    ));
  }
}
