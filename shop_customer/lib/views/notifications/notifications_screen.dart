import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notifications_provider.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) context.read<NotificationsProvider>().load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<NotificationsProvider>();
    final items = provider.notifications;

    Widget body;
    if (provider.isLoading && items.isEmpty) {
      body = const Center(child: CircularProgressIndicator());
    } else if (items.isEmpty) {
      body = ListView(children: [
        Padding(
          padding: const EdgeInsets.all(32),
          child: Text(
            provider.error ?? 'No updates yet. Order updates will appear here.',
            textAlign: TextAlign.center,
            style: const TextStyle(color: Color(0xFF7C8794)),
          ),
        ),
      ]);
    } else {
      body = ListView.separated(
        padding: const EdgeInsets.all(20),
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (_, i) {
          final n = items[i];
          return InkWell(
            onTap: () => provider.markAsRead(n),
            borderRadius: BorderRadius.circular(18),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: n.read ? Colors.white : const Color(0xFFF1F7F3),
                borderRadius: BorderRadius.circular(18),
              ),
              child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Container(
                  padding: const EdgeInsets.all(11),
                  decoration: const BoxDecoration(color: Color(0xFFDDEBE3), shape: BoxShape.circle),
                  child: Icon(n.read ? Icons.notifications_none : Icons.notifications_active, color: const Color(0xFF285D55)),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(n.title, style: TextStyle(fontWeight: n.read ? FontWeight.w600 : FontWeight.w800)),
                    const SizedBox(height: 5),
                    Text(n.message),
                    const SizedBox(height: 8),
                    Text(n.time, style: const TextStyle(fontSize: 11, color: Color(0xFF7C8794))),
                  ]),
                ),
              ]),
            ),
          );
        },
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Updates')),
      body: RefreshIndicator(onRefresh: provider.load, child: body),
    );
  }
}
